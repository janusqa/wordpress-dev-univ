class MyNotes {
    constructor() {
        if (document.querySelector('#my-notes')) {
            // this.editButtons = document.querySelectorAll('.edit-note');
            // this.deleteButtons = document.querySelectorAll('.delete-note');
            // this.saveButtons = document.querySelectorAll('.update-note');
            this.notes = document.querySelector('#my-notes');
            this.createButton = document.querySelector('.submit-note');
            this.noteLimitMessage = document.querySelector(
                '.note-limit-message'
            );

            this.events();
        }
    }

    events() {
        // this.deleteButtons.forEach((el) =>
        //     el.addEventListener('click', (e) => {
        //         e.preventDefault();
        //         this.deleteNote(e);
        //     })
        // );

        // this.editButtons.forEach((el) =>
        //     el.addEventListener('click', (e) => {
        //         e.preventDefault();
        //         this.editNote(e);
        //     })
        // );

        // this.saveButtons.forEach((el) =>
        //     el.addEventListener('click', (e) => {
        //         e.preventDefault();
        //         this.saveNote(e);
        //     })
        // );

        this.notes.addEventListener('click', (e) => this.noteHandler(e));

        this.createButton.addEventListener('click', (e) => {
            e.preventDefault();
            this.createNote(e);
        });
    }

    noteHandler(e) {
        if (
            e.target.classList.contains('delete-note') ||
            e.target.classList.contains('fa-trash-o')
        )
            this.deleteNote(e);

        if (
            e.target.classList.contains('edit-note') ||
            e.target.classList.contains('fa-pencil') ||
            e.target.classList.contains('fa-times')
        )
            this.editNote(e);

        if (
            e.target.classList.contains('update-note') ||
            e.target.classList.contains('fa-arrow-right')
        )
            this.saveNote(e);
    }

    async createNote(e) {
        const apiUrl = `${universityData.baseUrl}/wp-json/wp/v2`;

        try {
            const response = await fetch(`${apiUrl}/note`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': universityData.nonce,
                },
                body: JSON.stringify({
                    title: document.querySelector('.new-note-title').value,
                    content: document.querySelector('.new-note-body').value,
                    status: 'publish',
                }),
            });

            const json = await response.json();

            if (!response.ok)
                throw new Error(
                    json.data || `Response Status: ${response.status}`
                );

            document.querySelector('.new-note-title').value = '';
            document.querySelector('.new-note-body').value = '';
            document.querySelector('#my-notes').insertAdjacentHTML(
                'afterbegin',
                ` <li data-id="${json.id}" class="fade-in-calc">
                  <input readonly class="note-title-field" value="${json.title.raw}">
                  <span class="edit-note"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</span>
                  <span class="delete-note"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</span>
                  <textarea readonly class="note-body-field">${json.content.raw}</textarea>
                  <span class="update-note btn btn--blue btn--small"><i class="fa fa-arrow-right" aria-hidden="true"></i> Save</span>
                </li>`
            );

            // notice in the above HTML for the new <li> I gave it a class of fade-in-calc which will make it invisible temporarily so we can count its natural height

            let finalHeight; // browser needs a specific height to transition to, you can't transition to 'auto' height
            let newlyCreated = document.querySelector('#my-notes li');

            // give the browser 30 milliseconds to have the invisible element added to the DOM before moving on
            setTimeout(function () {
                finalHeight = `${newlyCreated.offsetHeight}px`;
                newlyCreated.style.height = '0px';
            }, 30);

            // give the browser another 20 milliseconds to count the height of the invisible element before moving on
            setTimeout(function () {
                newlyCreated.classList.remove('fade-in-calc');
                newlyCreated.style.height = finalHeight;
            }, 50);

            // wait the duration of the CSS transition before removing the hardcoded calculated height from the element so that our design is responsive once again
            setTimeout(function () {
                newlyCreated.style.removeProperty('height');
            }, 450);
        } catch (error) {
            this.noteLimitMessage.innerHTML = error.message;
            this.noteLimitMessage.classList.add('active');
            console.error(error.message);
        }
    }

    async saveNote(e) {
        const apiUrl = `${universityData.baseUrl}/wp-json/wp/v2`;
        const note = this.getNote(e.target, 'li');
        const noteId = note.getAttribute('data-id');

        if (note.getAttribute('data-state') == 'editable') {
            try {
                const response = await fetch(`${apiUrl}/note/${noteId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': universityData.nonce,
                    },
                    body: JSON.stringify({
                        title: note.querySelector('.note-title-field').value,
                        content: note.querySelector('.note-body-field').value,
                    }),
                });

                const json = await response.json();

                if (!response.ok)
                    throw new Error(
                        json.data || `Response Status: ${response.status}`
                    );

                // disable editing after saving
                this.makeNoteReadOnly(note);
            } catch (error) {
                console.error(error.message);
            }
        }
    }

    editNote(e) {
        const note = this.getNote(e.target, 'li');
        if (note.getAttribute('data-state') == 'editable') {
            this.makeNoteReadOnly(note);
        } else {
            this.makeNoteEditable(note);
        }
    }

    makeNoteEditable(note) {
        note.querySelector('.edit-note').innerHTML =
            '<i class="fa fa-times" aria-hidden="true"></i> Cancel';
        note.querySelector('.note-title-field').removeAttribute('readonly');
        note.querySelector('.note-body-field').removeAttribute('readonly');
        note.querySelector('.note-title-field').classList.add(
            'note-active-field'
        );
        note.querySelector('.note-body-field').classList.add(
            'note-active-field'
        );
        note.querySelector('.update-note').classList.add(
            'update-note--visible'
        );
        note.setAttribute('data-state', 'editable');
    }

    makeNoteReadOnly(note) {
        note.querySelector('.edit-note').innerHTML =
            '<i class="fa fa-pencil" aria-hidden="true"></i> Edit';
        note.querySelector('.note-title-field').setAttribute(
            'readonly',
            'true'
        );
        note.querySelector('.note-body-field').setAttribute('readonly', 'true');
        note.querySelector('.note-title-field').classList.remove(
            'note-active-field'
        );
        note.querySelector('.note-body-field').classList.remove(
            'note-active-field'
        );
        note.querySelector('.update-note').classList.remove(
            'update-note--visible'
        );
        note.setAttribute('data-state', 'cancel');
    }

    async deleteNote(e) {
        const apiUrl = `${universityData.baseUrl}/wp-json/wp/v2`;
        const note = this.getNote(e.target, 'li');
        const noteId = note.getAttribute('data-id');

        try {
            const response = await fetch(`${apiUrl}/note/${noteId}`, {
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

            if (json.numNotes <= 4) {
                this.noteLimitMessage.innerHTML = '';
                this.noteLimitMessage.classList.remove('active');
            }

            // remove note from ui
            setTimeout(() => note.classList.add('fade-out'), 20);
            setTimeout(() => note.remove(), 401);
        } catch (error) {
            console.error(error.message);
        }
    }

    getNote(element, target) {
        let currentElement = element;
        while (currentElement && currentElement !== document) {
            if (
                currentElement.tagName.toLowerCase() === target &&
                currentElement.hasAttribute('data-id')
            ) {
                return currentElement;
            }
            currentElement = currentElement.parentElement;
        }
        return null; // No matching target ancestor found
    }
}

export default MyNotes;
