/**
 * WordPress dependencies
 */
import { store, getContext } from '@wordpress/interactivity';
import { processGatewayResponse } from './scotia';

store('create-block', {
    actions: {
        processWindowMessage: function* (event) {
            const context = getContext();
            yield processGatewayResponse(event, context);
        },
    },
    callbacks: {},
});
