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
import { InspectorControls, useBlockProps, InspectorAdvancedControls } from '@wordpress/block-editor';

/**
 * Imports the necessary components that will be used to create
 * the user interface for the block's settings.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/panel/#panelbody
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/text-control/
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/toggle-control/
 */
import { PanelBody, TextControl } from '@wordpress/components';

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
	const { openidEndpoint, tokenEndpoint, apiClientId, apiClientSecret, queryId, successUrl, requestUriMethod, clientId, responseType, responseMode } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'openid4vp-exchange' ) }>
					<TextControl
						label={ __(
							'Query id',
							'openid4vp-exchange'
						) }
						value={ queryId }
						onChange={ ( value ) =>
							setAttributes( { queryId: value } )
						}
					/>
					<TextControl
						label={ __(
							'Success url',
							'openid4vp-exchange'
						) }
						value={ successUrl }
						onChange={ ( value ) =>
							setAttributes( { successUrl: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<InspectorAdvancedControls>
				<TextControl
					label={ __(
						'OpenID4VP Endpoint',
						'openid4vp-exchange'
					) }
					value={ openidEndpoint }
					onChange={ ( value ) =>
						setAttributes( { openidEndpoint: value } )
					}
				/>
				<TextControl
					label="Token endpoint"
					value={tokenEndpoint}
					onChange={( value ) =>
						setAttributes( { tokenEndpoint: value } )
					}
				/>
				<TextControl
					label="API client id"
					value={apiClientId}
					onChange={( value ) =>
						setAttributes( { apiClientId: value } )
					}
				/>
				<TextControl
					label="API client secret"
					type="password"
					value={apiClientSecret}
					onChange={( value ) =>
						setAttributes( { apiClientSecret: value } )
					}
				/>
				<TextControl
					label={ __(
						'Client id',
						'openid4vp-exchange'
					) }
					value={ clientId }
					onChange={ ( value ) =>
						setAttributes( { clientId: value } )
					}
				/>
				<TextControl
					label={ __(
						'Request URI method',
						'openid4vp-exchange'
					) }
					value={ requestUriMethod }
					onChange={ ( value ) =>
						setAttributes( { requestUriMethod: value } )
					}
				/>
				<TextControl
					label={ __(
						'Response type',
						'openid4vp-exchange'
					) }
					value={ responseType }
					onChange={ ( value ) =>
						setAttributes( { responseType: value } )
					}
				/>
				<TextControl
					label={ __(
						'Response mode',
						'openid4vp-exchange'
					) }
					value={ responseMode }
					onChange={ ( value ) =>
						setAttributes( { responseMode: value } )
					}
				/>
			</InspectorAdvancedControls>
			<p {...useBlockProps()}>
				<form id="org-wallet-form">
					<input type="text" id="org-wallet-url" name="walletUrl" placeholder="Enter wallet URL" />
					<button type="button" id="org-wallet-submit">Connect to wallet</button>
				</form>
			</p>
		</>
	);
}
