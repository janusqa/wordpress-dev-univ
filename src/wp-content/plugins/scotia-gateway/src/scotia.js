const receiveMessage = async (event) => {
    if (event.origin !== 'https://test.ipg-online.com') return;

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
    } catch (error) {
        console.log(error.message);
        // show error page with
    }
};

window.addEventListener('message', receiveMessage, false);
