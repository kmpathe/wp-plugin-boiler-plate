import { registerBlockType } from '@wordpress/blocks';

import demoText from './components/demo-text/block.json';
import EditDemoText from './components/demo-text/EditDemoText';
import SaveDemoText from './components/demo-text/SaveDemoText';

registerBlockType(demoText, {
    edit: EditDemoText,
    save: SaveDemoText,
});
