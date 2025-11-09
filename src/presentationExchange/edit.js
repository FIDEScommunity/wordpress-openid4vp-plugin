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
import { InspectorControls, InspectorAdvancedControls, useBlockProps } from '@wordpress/block-editor';

/**
 * Imports the necessary components that will be used to create
 * the user interface for the block's settings.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/panel/#panelbody
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/text-control/
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/toggle-control/
 */
import { PanelBody, TextControl, ToggleControl, NumberControl } from '@wordpress/components';

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
	const { openidEndpoint, tokenEndpoint, apiClientId, apiClientSecret, queryId, clientId, requestUriMethod, responseType, responseMode, qrCodeEnabled, qrSize, qrColorDark, qrColorLight, qrPadding, successUrl } = attributes;

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
				<ToggleControl
					label={ __(
						'QR code',
						'openid4vp-exchange'
					) }
					help={
						qrCodeEnabled
							? 'Show QR Code.'
							: 'Don\'t show QR Code.'
					}
					checked={ qrCodeEnabled }
					onChange={ ( value ) =>
						setAttributes( { qrCodeEnabled: value } )
					}
				/>
				{ qrCodeEnabled &&
					<TextControl
						label={ __(
							'QR size',
							'openid4vp-exchange'
						) }
						value={ qrSize }
						onChange={ ( value ) =>
							setAttributes( { qrSize: parseInt( value, 10 ) } )
						}
					/>
				}
				{ qrCodeEnabled &&
					<TextControl
					label={ __(
						'QR color dark',
						'openid4vp-exchange'
					) }
					value={ qrColorDark }
					onChange={ ( value ) =>
						setAttributes( { qrColorDark: value } )
					}
					/>
				}
				{ qrCodeEnabled && <TextControl
					label={ __(
						'QR color light',
						'openid4vp-exchange'
					) }
					value={ qrColorLight }
					onChange={ ( value ) =>
						setAttributes( { qrColorLight: value } )
					}
				/>}
				{ qrCodeEnabled && <TextControl
					label={ __(
						'QR padding',
						'openid4vp-exchange'
					) }
					value={ qrPadding }
					onChange={ ( value ) =>
						setAttributes( { qrPadding: parseInt( value, 10 ) } )
					}
				/>}
			</InspectorAdvancedControls>
			<p {...useBlockProps()}><img width={qrSize} decoding="async"
										 src="data:data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAPoAAAD6AQAAAACgl2eQAAAEL0lEQVR4Xu2YPa6rMBBGJ6KgCxuw5G24Y0thAwlsALbkzttAYgPQUVj4nSG3ILd4r7C7FytSfjgR9njmm89I+vvw8vuXX+MLvMcXeI8v8B7/FdBLlfY0hTS21bSbh6TBz3JeKgVMaRnbKK25hfjyC1dvfuEvBYFRls1bsMHLbZ8bvxyy9K4wMHhzl9j5ePN2rUVceWCVJYX5tksT0lrzKgywwL62h7M927TLayeqv0OdCZAPo1s+X78TJhNgbME8anN3aax/MvA9SgG9zCTbvTUPJ82+HHWU2nShJDCE+GjlIcu0S7fblXiSfkWBldDVBDN2qSIxJg/MxEoCk49NIKVNE6QLszi7BX4pCYxObsH2bdqSTb46ai1/crsgcLTm6ZYtzPfaDrsRZ7pEHZUE+rNMVs1kds28kjxd2nxJgDEkQ0pIa8eamcizTtd8KAHMDVmBlJHScu4Umn9RmHxgSHZkdVqeNumukXvyOv9ZDNhnKpF6WSU+3fwK86OtrpHMB3oxtzQ/hGBWU6h6mDR3HzqZCxBD3SNqM9i0s1La1nyNZD7Q6wKrLUWqcqJnOWI7dx/LzAUO1L6t2LLJzy/PJbmLKQsMeySTX8FuqUrJnvlsmksk8wHeOmxJoCpZLzofGxbblgQGz33t0WrhJL1EVi9HUYDVHaJ9imncUZtEYHFcJYFV+yBzQCSxizQUBE29SkFgU/ODEbV81xjW8e5ELmqfD3DTQd+xcKo2K+s9PXBB4NDaRB4pTLVAKSz04ukSyXxgFF5oS8IiTgkvZBr/ofb5wCFnJJ1RR+qZDFUj90sk8wE6iGqLzoG7G6lx1+naefMB/OHayt1RnvMdg63pwWRKAgcBDMyB2pRHO+Orux0LURLYtH1EET7AUEeoQbyfl0oBGEVCh78ShyM1bNPw2XkLADWtcBnFCAZ7x7eLKvMlkvnAoIYE2zA/KR90Hq/I70WBKdCkcCm2r9VRbwF5Wa5qnw+oC9Lmy+kj0rnovMzkoy3mA0Ge6n8iLoW7T+p+EZmSAKrSII962FRHPex22z8jmQ1wEicNqP1uFzkj2QSaS0ng0DSg284qXN72qvmfxZsPOHmoyHO0wZqiA9QO7rEkQCsZW3KsopVsiaygUdrpssx8YFRzgjMhE8hqyicimDe9UgzY0Pkax855jW+IgGaFpkQ5QB1pXa3OnjtVTfrMijwvCfROz02vpHM4aj1AKXyOYkBNJFH7+M5qdUFCnpcEkpYnrbZSI+HwD5Gr42WZ+QCdd/CoFovVGHahOtzSFwUmTvpJnRU5wBlEV6q9uCQwit5x4HTw40jtiCNtCwM036eWj3lRQXRefdpQGJh8fOjpgP5bIcJr/XGkzQf0Ea4eauYmVSTG5uOLD0UB8oGeS+i285lYCnJLn7WZDfxlfIH3+ALv8QXe4wu8x7+BP6qeIKKvI9tZAAAAAElFTkSuQmCC"/>
			</p>
		</>
	);
}
