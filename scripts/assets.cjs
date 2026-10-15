const fs = require('node:fs');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const sass = require('sass');
const { minify } = require('terser');
const archiver = require('archiver');

const root = path.resolve(__dirname, '..');
const pkg = require('../package.json');
const slug = pkg.name;
if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) {
    throw new Error('package.json name must be a plugin slug.');
}
const outputDir = path.join(root, slug);
const zipPath = path.join(root, slug + '.zip');
const marker = '.generated-plugin';
const contexts = ['admin', 'public'];

const walk = (directory) => {
    if (!fs.existsSync(directory)) return [];
    return fs.readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
        const file = path.join(directory, entry.name);
        if (entry.isSymbolicLink()) throw new Error('Symlinks are not supported: ' + file);
        return entry.isDirectory() ? walk(file) : [file];
    });
};

const write = (file, value) => {
    fs.mkdirSync(path.dirname(file), { recursive: true });
    fs.writeFileSync(file, value);
};

const compileBlocks = async () => {
    const command = require.resolve('@wordpress/scripts/bin/wp-scripts.js');
    const result = spawnSync(process.execPath, [command, 'build'], {
        cwd: root,
        stdio: 'inherit',
    });
    if (result.error || result.status !== 0) throw new Error('Gutenberg compilation failed.');
};
const compileAssets = async () => {
    const bases = contexts.map((context) => path.join(root, 'assets', context));
    for (const base of bases) {
        const targets = [
            { source: path.join(base, 'src', 'scss'), dest: path.join(base, 'dist', 'css'), type: 'sass' },
            { source: path.join(base, 'src', 'js'), dest: path.join(base, 'dist', 'js'), type: 'js' },
        ];
        for (const target of targets) {
            const expected = new Set();
            for (const source of walk(target.source)) {
                const isSass = target.type === 'sass';
                if (isSass && (!/\.(scss|sass)$/.test(source) || path.basename(source).startsWith('_'))) continue;
                if (!isSass && !source.endsWith('.js')) continue;
                const relative = path.relative(target.source, source).replace(/\.[^.]+$/, isSass ? '.min.css' : '.min.js');
                const destination = path.join(target.dest, relative);
                const content = isSass
                    ? sass.compile(source, { style: 'compressed' }).css
                    : (await minify(fs.readFileSync(source, 'utf8'), { format: { comments: false } })).code;
                write(destination, content);
                expected.add(destination);
                if (!isSass) {
                    const assetSource = source.replace(/\.js$/, '.asset.php');
                    if (fs.existsSync(assetSource)) {
                        const assetDestination = destination.replace(/\.js$/, '.asset.php');
                        write(assetDestination, fs.readFileSync(assetSource));
                        expected.add(assetDestination);
                    }
                }
            }
            for (const file of walk(target.dest)) {
                if (/\.min\.(css|js)$/.test(file) && !expected.has(file)) fs.unlinkSync(file);
            }
        }
    }
    await compileBlocks();
    console.log('Assets compiled.');
};

const copyFile = (source) => {
    fs.copyFileSync(source, (() => {
        const destination = path.join(outputDir, path.relative(root, source));
        fs.mkdirSync(path.dirname(destination), { recursive: true });
        return destination;
    })());
};

const build = async () => {
    await compileAssets();
    if (fs.existsSync(outputDir)) {
        if (fs.lstatSync(outputDir).isSymbolicLink() || !fs.existsSync(path.join(outputDir, marker))) {
            throw new Error('Refusing to replace an existing non-generated folder: ' + outputDir);
        }
        fs.rmSync(outputDir, { recursive: true });
    }
    fs.mkdirSync(outputDir);
    write(path.join(outputDir, marker), slug);

    for (const entry of fs.readdirSync(root, { withFileTypes: true })) {
        if (entry.isFile() && /\.(php|md|txt)$/.test(entry.name)) copyFile(path.join(root, entry.name));
    }
    for (const directory of ['src', 'views', 'languages']) {
        for (const file of walk(path.join(root, directory))) {
            if (path.basename(file) !== '.gitkeep') copyFile(file);
        }
    }
    for (const file of walk(path.join(root, 'assets'))) {
        const parts = path.relative(path.join(root, 'assets'), file).split(path.sep);
        if (parts.includes('src') || parts.includes('scss') || parts.includes('sass')) continue;
        if (path.basename(file) === '.gitkeep' || file.endsWith('.map')) continue;
        copyFile(file);
    }

    for (const file of walk(path.join(root, 'blocks'))) {
        const parts = path.relative(path.join(root, 'blocks'), file).split(path.sep);
        const isMetadata = parts[0] === 'src' && file.endsWith('block.json');
        const isCompiled = parts[0] === 'build';
        if ((!isMetadata && !isCompiled) || path.basename(file) === '.gitkeep' || file.endsWith('.map')) continue;
        copyFile(file);
    }
    const composerPath = path.join(root, 'composer.json');
    if (fs.existsSync(composerPath)) {
        const composer = JSON.parse(fs.readFileSync(composerPath, 'utf8'));
        const runtimePackages = Object.keys(composer.require || {}).filter((name) => name !== 'php' && !name.startsWith('ext-') && !name.startsWith('lib-'));
        if (runtimePackages.length) {
            fs.copyFileSync(composerPath, path.join(outputDir, 'composer.json'));
            const lock = path.join(root, 'composer.lock');
            if (fs.existsSync(lock)) fs.copyFileSync(lock, path.join(outputDir, 'composer.lock'));
            const command = process.platform === 'win32' ? 'composer.bat' : 'composer';
            const result = spawnSync(command, ['install', '--no-dev', '--prefer-dist', '--optimize-autoloader', '--no-interaction', '--no-scripts'], {
                cwd: outputDir,
                stdio: 'inherit',
                shell: process.platform === 'win32',
            });
            if (result.error || result.status !== 0) throw new Error('Production Composer installation failed.');
        }
    }
    console.log('Plugin ready: ' + outputDir);
};

const deploy = async () => {
    await build();
    const temporaryZip = zipPath + '.tmp';
    try {
        await new Promise((resolve, reject) => {
            const destination = fs.createWriteStream(temporaryZip);
            const archive = archiver('zip', { zlib: { level: 9 } });
            destination.on('close', resolve);
            destination.on('error', reject);
            archive.on('error', reject);
            archive.on('warning', reject);
            archive.pipe(destination);
            for (const file of walk(outputDir)) {
                if (path.basename(file) === marker) continue;
                archive.file(file, { name: slug + '/' + path.relative(outputDir, file).split(path.sep).join('/') });
            }
            archive.finalize().catch(reject);
        });
        fs.renameSync(temporaryZip, zipPath);
    } catch (error) {
        if (fs.existsSync(temporaryZip)) fs.unlinkSync(temporaryZip);
        throw error;
    }
    console.log('WordPress ZIP ready: ' + zipPath);
};

module.exports = { compileAssets, build, deploy };
