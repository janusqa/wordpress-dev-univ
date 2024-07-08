import axios from 'axios';

class Search {
    constructor() {
        this.addSearchUI();
        this.searchOverlay = document.querySelector('.search-overlay');
        this.openButton = document.querySelectorAll('.js-search-trigger');
        this.closeButton = document.querySelector('.search-overlay__close');
        this.searchField = document.querySelector('#search-term');
        this.searchResults = document.querySelector('#search-overlay__results');

        this.isOverLayOpen = false;
        this.isSpinnerVisible = false;

        this.debounce;
        this.previousSearchText;
        this.events();
    }

    events() {
        this.openButton.forEach((el) => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                this.openOverlay();
            });
        });
        this.closeButton.addEventListener('click', (e) => this.closeOverlay());
        document.addEventListener('keydown', (e) => this.keyPressDispatcher(e));
        this.searchField.addEventListener('keyup', (e) =>
            this.processSearchTerm()
        );
    }

    openOverlay() {
        this.searchField.value = '';
        this.searchResults.innerHTML = '';
        this.searchOverlay.classList.add('search-overlay--active');
        document.body.classList.add('body-no-scroll');
        this.isOverLayOpen = true;
    }

    closeOverlay() {
        this.searchOverlay.classList.remove('search-overlay--active');
        document.body.classList.remove('body-no-scroll');
        this.isOverLayOpen = false;
    }

    keyPressDispatcher(e) {
        if (e.keyCode === 27 && this.isOverLayOpen) this.closeOverlay();
    }

    processSearchTerm(e) {
        if (this.searchField.value !== this.previousSearchText) {
            clearTimeout(this.debounce);

            if (this.searchField.value) {
                if (!this.isSpinnerVisible) {
                    this.searchResults.innerHTML =
                        '<div class="spinner-loader"></div>';
                    this.isSpinnerVisible = true;
                }
                this.debounce = setTimeout(() => this.getResults(), 500);
            } else {
                this.searchResults.innerHTML = '';
                this.isSpinnerVisible = false;
            }
        }

        this.previousSearchText = this.searchField.value;
    }

    async getResults() {
        const apiUrl = `${universityData.baseUrl}/wp-json/university/v1`;

        if (this.searchField.value.trim().length > 3) {
            const response = await axios.get(
                `${apiUrl}/search?term=${this.searchField.value}`
            );
            const results = response.data;

            this.searchResults.innerHTML = `
                <div class="row">
                    <div class="one-third">
                        <h2 class="search-overlay__section-title">General Information</h2>
                        ${
                            results['posts'].length
                                ? '<ul class="link-list min-list">'
                                : '<p>No results found</p>'
                        }
                        ${results['posts']
                            .map(
                                (result) =>
                                    `<li><a href="${result.permalink}">${
                                        result.title
                                    }</a> ${
                                        result.postType == 'post'
                                            ? ` by ${result.authorName}`
                                            : ''
                                    }</li>`
                            )
                            .join('')}
                        ${results['posts'].length ? '</ul>' : ''}
                    </div>
                    <div class="one-third">
                        <h2 class="search-overlay__section-title">Programs</h2>
                            ${
                                results['programs'].length
                                    ? '<ul class="link-list min-list">'
                                    : '<p>No results found</p>'
                            }
                            ${results['programs']
                                .map(
                                    (result) =>
                                        `<li><a href="${result.permalink}">${result.title}</a></li>`
                                )
                                .join('')}
                            ${
                                results['programs'].length ? '</ul>' : ''
                            }                            
                        <h2 class="search-overlay__section-title">Professors</h2>
                            ${
                                results['professors'].length
                                    ? '<ul class="professor-cards">'
                                    : '<p>No results found</p>'
                            }
                            ${results['professors']
                                .map(
                                    (result) =>
                                        `<li class="professor-card__list-item">
                                            <a class="professor-card" href="${result.permalink}">
                                                <img class="professor-card__image" src="${result.image}" />
                                                <span class="professor-card__name">${result.title}</span>
                                            </a>
                                        </li>`
                                )
                                .join('')}
                            ${
                                results['professors'].length ? '</ul>' : ''
                            }                                
                    </div>
                    <div class="one-third">
                        <h2 class="search-overlay__section-title">Campuses</h2>
                            ${
                                results['campuses'].length
                                    ? '<ul class="link-list min-list">'
                                    : '<p>No results found</p>'
                            }
                            ${results['campuses']
                                .map(
                                    (result) =>
                                        `<li><a href="${result.permalink}">${result.title}</a></li>`
                                )
                                .join('')}
                            ${
                                results['campuses'].length ? '</ul>' : ''
                            }                                                        
                        <h2 class="search-overlay__section-title">Events</h2>
                            ${
                                results['events'].length
                                    ? ''
                                    : '<p>No results found</p>'
                            }
                            ${results['events']
                                .map(
                                    (result) =>
                                        `<div class="event-summary">
                                                <a class="event-summary__date t-center" href="${result.permalink}">
                                                    <span class="event-summary__month">${result.month}</span>
                                                    <span class="event-summary__day">${result.day}</span>
                                                </a>
                                                <div class="event-summary__content">
                                                    <h5 class="event-summary__title headline headline--tiny"><a href="${result.permalink}">${result.title}</a></h5>
                                                    <p>
                                                        ${result.summary}&nbsp;<a href="${result.permalink}" class="nu gray">Learn more</a>
                                                    </p>
                                                </div>
                                            </div>`
                                )
                                .join('')}                             
                    </div>
                </div>
            `;
        }

        this.isSpinnerVisible = false;
    }

    addSearchUI() {
        document.body.insertAdjacentHTML(
            'beforeend',
            `<div class="search-overlay">
                <div class="search-overlay__top">
                    <div class="container">
                        <i class="fa fa-search search-overlay__icon" aria-hidden="true"></i>
                        <input type="text" id="search-term" class="search-term" placeholder="What are you looking for?" />
                    </div>
                    <i class="fa fa-window-close search-overlay__close" aria-hidden="true"></i>
                </div>
                <div class="container">
                    <div id="search-overlay__results"></div>
                </div>
            </div>`
        );
    }
}

export default Search;
