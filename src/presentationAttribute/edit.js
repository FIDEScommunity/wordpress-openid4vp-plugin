/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * Imports the InspectorControls component, which is used to wrap
 * the block's custom controls that will appear in in the Settings
 * Sidebar when the block is selected.
 *
 * Also imports the React hook that is used to mark the block wrapper
 * element. It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#inspectorcontrols
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';

/**
 * Imports the necessary components that will be used to create
 * the user interface for the block's settings.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/panel/#panelbody
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/text-control/
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/toggle-control/
 */
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

/**
 * Imports the useEffect React Hook. This is used to set an attribute when the
 * block is loaded in the Editor.
 *
 * @see https://react.dev/reference/react/useEffect
 */
import { useEffect } from 'react';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Properties passed to the function.
 * @param {Object}   props.attributes    Available block attributes.
 * @param {Function} props.setAttributes Function that updates individual attributes.
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const { credentialQueryId, attributeLabel, attributeName } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Settings', 'openid4vp-attribute')}>
					<TextControl
						label={ __(
							'Credential query id',
							'openid4vp-attribute'
						) }
						value={ credentialQueryId }
						onChange={ ( value ) =>
							setAttributes( { credentialQueryId: value } )
						}
					/>
					<TextControl
						label={ __(
							'VP attribute label',
							'openid4vp-attribute'
						) }
						value={ attributeLabel }
						onChange={ ( value ) =>
							setAttributes( { attributeLabel: value } )
						}
					/>
					<TextControl
						label={ __(
							'VP attribute name',
							'openid4vp-attribute'
						) }
						value={ attributeName }
						onChange={ ( value ) =>
							setAttributes( { attributeName: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<p {...useBlockProps()}>{attributeLabel ? attributeLabel + ': ' : ''}&lt;{credentialQueryId}.{attributeName}&gt;</p>
		</>
	);
}
