export const processGatewayResponse = async (event, context) => {
    console.log('scotia.js', event);
    console.log('scotia.js', context);

    if (event.origin !== 'https://test.ipg-online.com') return;

    const iframe = document.querySelector("iframe[name='scotiaFrame']");

    try {
        const response = await fetch(event.data.redirectURL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                // 'X-WP-Nonce': universityData.nonce,
            },
            body: JSON.stringify(event.data.elementArr),
        });

        const result = await response.json();

        if (!response.ok)
            throw new Error(
                `Error: ${result.message}` ||
                    `Error: ${response} (${response.status})`
            );

        //show success page
        iframe.contentDocument.body.innerHTML = result.data.html_content;
    } catch (error) {
        // show error page with
        iframe.contentDocument.body.innerHTML = result.data.html_content;
    }
};

// window.addEventListener('message', receiveMessage, false);
