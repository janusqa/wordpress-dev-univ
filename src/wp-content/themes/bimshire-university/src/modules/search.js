import $ from 'jquery';

class Search {
    constructor() {
        this.addSearchUI();
        this.searchOverlay = $('.search-overlay');
        this.openButton = $('.js-search-trigger');
        this.closeButton = $('.search-overlay__close');
        this.searchField = $('#search-term');
        this.searchResults = $('.search-overlay__results');

        this.isOverLayOpen = false;
        this.isSpinnerVisible = false;

        this.debounce;
        this.previousSearchText;
        this.events();
    }

    events() {
        this.openButton.on('click', this.openOverlay.bind(this));
        this.closeButton.on('click', this.closeOverlay.bind(this));
        $(document).on('keydown', this.keyPressDispatcher.bind(this));
        this.searchField.on('keyup', this.processSearchTerm.bind(this));
    }

    openOverlay() {
        this.searchField.val('');
        this.searchResults.html('');
        this.searchOverlay.addClass('search-overlay--active');
        $('body').addClass('body-no-scroll');
        this.isOverLayOpen = true;
    }

    closeOverlay() {
        this.searchOverlay.removeClass('search-overlay--active');
        $('body').removeClass('body-no-scroll');
        this.isOverLayOpen = false;
    }

    keyPressDispatcher(e) {
        if (e.keyCode === 27 && this.isOverLayOpen) this.closeOverlay();
    }

    processSearchTerm(e) {
        if (this.searchField.val() !== this.previousSearchText) {
            clearTimeout(this.debounce);

            if (this.searchField.val()) {
                if (!this.isSpinnerVisible) {
                    this.searchResults.html(
                        '<div class="spinner-loader"></div>'
                    );
                    this.isSpinnerVisible = true;
                }
                this.debounce = setTimeout(this.getResults.bind(this), 500);
            } else {
                this.searchResults.html('');
                this.isSpinnerVisible = false;
            }
        }

        this.previousSearchText = this.searchField.val();
    }

    getResults() {
        const apiUrl = `${universityData.baseUrl}/wp-json/wp/v2`;
        if (this.searchField.val().trim().length > 3) {
            $.when(
                $.getJSON(`${apiUrl}/posts?search=${this.searchField.val()}`),
                $.getJSON(`${apiUrl}/pages?search=${this.searchField.val()}`)
            ).then(
                (post, pages) => {
                    const combinedResults = [...post[0], ...pages[0]];
                    this.searchResults.html(`
                        <h2 class="search-overlay__section-title">General Information</h2>
                        ${
                            combinedResults.length
                                ? '<ul class="link-list min-list">'
                                : '<p>No results found</p>'
                        }
                        ${combinedResults
                            .map(
                                (result) =>
                                    `<li><a href="${result.link}">${result.title.rendered}</a></li>`
                            )
                            .join('')}
                        ${combinedResults.length ? '</ul>' : ''}
                    `);
                },
                () =>
                    this.searchResults.html(
                        '<p>Unexpected error; please try again.</p>'
                    )
            );
        }

        this.isSpinnerVisible = false;
    }

    addSearchUI() {
        $('body').append(`
            <div class="search-overlay">
                <div class="search-overlay__top">
                    <div class="container">
                        <i class="fa fa-search search-overlay__icon" aria-hidden="true"></i>
                        <input type="text" id="search-term" class="search-term" placeholder="What are you looking for?" />
                    </div>
                    <i class="fa fa-window-close search-overlay__close" aria-hidden="true"></i>
                </div>
                <div class="container">
                    <div class="search-overlay__results"></div>
                </div>
            </div>            
        `);
    }
}

export default Search;
