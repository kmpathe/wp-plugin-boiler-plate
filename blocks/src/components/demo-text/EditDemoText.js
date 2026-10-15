import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const EditDemoText = ({ attributes, setAttributes }) => {
    const blockProps = useBlockProps();

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Block settings', 'wp-plugin-boiler-plate')}>
                    <TextControl
                        label={__('Text', 'wp-plugin-boiler-plate')}
                        value={attributes.text}
                        onChange={(text) => setAttributes({ text })}
                        __nextHasNoMarginBottom
                        __next40pxDefaultSize
                    />
                </PanelBody>
            </InspectorControls>
            <p {...blockProps}>
                {attributes.text || __('Enter text in the block settings.', 'wp-plugin-boiler-plate')}
            </p>
        </>
    );
};

export default EditDemoText;
