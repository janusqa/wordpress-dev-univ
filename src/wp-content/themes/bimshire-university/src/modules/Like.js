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

            const result = await response.json();

            if (!response.ok)
                throw new Error(
                    result.message || `Response Status: ${response.status}`
                );

            const likeCountEl = likeBox.querySelector('.like-count');
            let likeCount = parseInt(likeCountEl.textContent, 10);
            if (!isNaN(likeCount))
                likeBox.querySelector('.like-count').textContent = ++likeCount;
            likeBox.setAttribute('data-exists', 'yes');
            likeBox.setAttribute('data-like-id', result.data.ID);
        } catch (error) {
            console.error(error.message);
        }
    }

    async unLike(likeBox) {
        const apiUrl = `${universityData.baseUrl}/wp-json/university/v1`;
        const like_id = likeBox.getAttribute('data-like-id');

        try {
            const response = await fetch(`${apiUrl}/likes/${like_id}`, {
                method: 'DELETE',
                headers: {
                    'X-WP-Nonce': universityData.nonce,
                },
            });

            const result = await response.json();

            if (!response.ok)
                throw new Error(
                    result.message || `Response Status: ${response.status}`
                );

            const likeCountEl = likeBox.querySelector('.like-count');
            let likeCount = parseInt(likeCountEl.textContent, 10);
            if (!isNaN(likeCount))
                likeBox.querySelector('.like-count').textContent = --likeCount;
            likeBox.setAttribute('data-exists', 'no');
            likeBox.setAttribute('data-like-id', '');
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
