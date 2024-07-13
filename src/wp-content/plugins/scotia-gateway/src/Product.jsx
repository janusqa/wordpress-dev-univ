import {
    TextControl, Button
} from '@wordpress/components';

const Product = ({ attributes, setAttributes }) => {
    return <div>
        <TextControl
            label="Meeting Code:"
            value={attributes.product_code}
            onChange={(value) => setAttributes({ product_code: value })}
            style={{ fontSize: '20px' }}
        />
        <TextControl
            label="Price (USD):"
            value={attributes.product_price}
            onChange={(value) => setAttributes({ product_price: value })}
            style={{ fontSize: '20px' }}
        />
        <Button
            variant="primary"
            onClick={() => {
                setAttributes({ answers: [...attributes.answers, ''] });
            }}
        >Create / Edit Product</Button>
    </div>
};

export default Product;