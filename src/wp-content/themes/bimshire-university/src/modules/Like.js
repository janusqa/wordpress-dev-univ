class Like {
    constructor() {
        if (document.querySelectorAll('.like-box')) {
            this.likeBox = document.querySelectorAll('.like-box');
            this.events();
        }
    }

    events() {
        this.likeBox.forEach((el) =>
            el.addEventListener('click', (e) => {
                e.preventDefault();
                this.processlike(e);
            })
        );
    }

    processlike(e) {
        const likeBox = this.getlikeBox(e, 'span', 'data-exists');
        if (likeBox) {
            const likedStatus = likeBox.getAttribute('data-exists');
            if (likedStatus === 'yes') this.unLike(likeBox);
            else this.like(likeBox);
        }
    }

    async like(likeBox) {
        const apiUrl = `${universityData.baseUrl}/wp-json/university/v1`;
        const liked_professor_id = likeBox.getAttribute('data-professor-id');

        try {
            const response = await fetch(`${apiUrl}/likes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': universityData.nonce,
                },
                body: JSON.stringify({
                    liked_professor_id,
                }),
            });

            const json = await response.json();

            if (!response.ok)
                throw new Error(
                    json.data || `Response Status: ${response.status}`
                );

            console.log(json);
        } catch (error) {
            console.error(error.message);
        }
    }

    async unLike(likeBox) {
        const apiUrl = `${universityData.baseUrl}/wp-json/university/v1`;
        const liked_professor_id = likeBox.getAttribute('data-professor-id');

        try {
            const response = await fetch(`${apiUrl}/likes`, {
                method: 'DELETE',
                headers: {
                    'X-WP-Nonce': universityData.nonce,
                },
            });

            const json = await response.json();

            if (!response.ok)
                throw new Error(
                    json.data || `Response Status: ${response.status}`
                );

            console.log(json);
        } catch (error) {
            console.error(error.message);
        }
    }

    getlikeBox(e, target, attribute) {
        let currentEl = e.target;
        while (currentEl && currentEl !== document) {
            if (
                currentEl.tagName.toLowerCase() === target &&
                currentEl.hasAttribute(attribute)
            ) {
                return currentEl;
            }
            currentEl = currentEl.parentElement;
        }
        return null; // No matching target ancestor found
    }
}

export default Like;
