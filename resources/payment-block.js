import { decodeEntities } from '@wordpress/html-entities';
import { RawHTML } from '@wordpress/element';

const { registerPaymentMethod } = window.wc.wcBlocksRegistry
const { getSetting } = window.wc.wcSettings
const settings = getSetting( 'aplazame_data', {} )
const label = decodeEntities( settings.title )

const Content = () => {
  return <RawHTML>{ settings.description || '' }</RawHTML>;
}

const Label = ( props ) => {
  const { PaymentMethodLabel } = props.components
  return <PaymentMethodLabel text={ label } />
}

registerPaymentMethod(
    {
    name: "aplazame",
    label: <Label />,
    content: <Content />,
    edit: <Content />,
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
      features: settings.supports,
    }
  }
)
