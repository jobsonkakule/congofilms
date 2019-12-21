import {Flipper, spring} from 'flip-toolkit'

/**
 * @property {HTMLElement} pagination
 * @property {HTMLElement} content
 * @property {HTMLElement} title
 * @property {number} page
 * @property {boolean} moreNav
 */
export default class PostToggle {

    /**
     *
     * @param {HTMLElement|null} element
     */
    constructor (element) {
        if (element === null) {
            return
        }
        this.pagination = element.querySelector('.js-show-pagination')
        this.content = element.querySelector('.js-show-content')
        this.title = element.querySelector('.js-show-title')
        this.page = parseInt(new URLSearchParams(window.location.search).get('page') || 1)
        this.moreNave = this.page === 1
        this.bindEvents()
    }

    /**
     * Add behaviour on differents elements
     */
    bindEvents () {
        const aClickListener = e => {
            if (e.target.tagName === 'A') {
                e.preventDefault()
                $('.cat-high-toggle a').removeClass('cat-high-selected')
                this.loadUrl(e.target.getAttribute('href'))
                e.target.classList.add('cat-high-selected')
            }
        }
        this.title.addEventListener('click', e => {
            aClickListener(e)
            this.page = 1
        })
        this.pagination.addEventListener('click', e => {
            if (e.target.tagName === 'A') {
                e.preventDefault()
                this.loadUrl(e.target.getAttribute('href'))
            }
        })
        if (this.moreNave) {
            this.pagination.innerHTML = '<button class="btn btn-primary">Voir plus</button>'
            this.pagination.querySelector('button').addEventListener('click', this.loadMore.bind(this))
        } else {
            this.pagination.addEventListener('click', aClickListener)
        }
    }

    async loadMore () {
        const button = this.pagination.querySelector('button')
        button.setAttribute('disabled', 'disabled')
        this.page++
        const url = new URL(window.location.href)
        const params = new URLSearchParams(url.search)
        params.set('page', this.page)
        await this.loadUrl(url.pathname + '?' + params.toString(), true)
        button.removeAttribute('disabled')
    }

    async loadUrl (url, append = false) {
        this.showLoader()
        const params = new URLSearchParams(url.split('?')[1] || '')
        params.set('ajax', 1)
        const response = await fetch(url.split('?')[0] + '?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        if (response.status >= 200 && response.status < 300) {
            const data = await response.json()
            this.flipContent(data.content, append)
            if (!this.moreNave) {
                this.pagination.innerHTML = data.pagination
            } else if (this.page === data.pages) {
                this.pagination.style.display = 'none'
            } else {
                this.pagination.style.display = null
            }
            params.delete('ajax')
            history.replaceState({}, '', url.split('?')[0] + '?' + params.toString())
        } else {
            console.error(response)
        }
        this.hideLoader()
    }

    /**
     * Replace elements of grid with flip animation
     * @param {string} content
     */
    flipContent (content, append) {
        const springConfig = 'gentle'
        const exitSpring = function (element, index, onComplete) {
            spring({
              config: 'stiff',
              values: {
                translateY: [0, -20],
                opacity: [1, 0]
              },
              onUpdate: ({ translateY, opacity }) => {
                element.style.opacity = opacity;
                element.style.transform = `translateY(${translateY}px)`;
              },
            //   delay: index * 25,
              onComplete
            });
        }
        const appearSpring = function (element, index) {
            spring({
              config: 'stiff',
              values: {
                translateY: [20, 0],
                opacity: [0, 1]
              },
              onUpdate: ({ translateY, opacity }) => {
                element.style.opacity = opacity;
                element.style.transform = `translateY(${translateY}px)`;
              },
              delay: index * 20
            });
        }
        const flipper = new Flipper({
            element: this.content
        })
        this.content.children.forEach(element => {
            if (element.getAttribute('id') !== 'spinner-none') {
                flipper.addFlipped({
                    element,
                    spring: springConfig,
                    flipId: element.id,
                    shouldFlip: false,
                    onExit: exitSpring
                })
            }
        })
        flipper.recordBeforeUpdate()
        if (append) {
            this.content.innerHTML += content
        } else {
            this.content.innerHTML = content
        }
        this.content.children.forEach(element => {
            if (element.getAttribute('id') !== 'spinner-none') {
                flipper.addFlipped({
                    element,
                    spring: springConfig,
                    flipId: element.id,
                    onAppear: appearSpring
                })
            }
        })
        flipper.update()
    }

    showLoader() {
        this.content.classList.add('is-loading')
        const loader = this.content.querySelector('.js-loading')
        if (loader === null) {
            return
        }
        loader.setAttribute('aria-hidden', 'false')
        loader.style.display = null
    }


    hideLoader() {
        this.content.classList.remove('is-loading')
        const loader = this.content.querySelector('.js-loading')
        if (loader === null) {
            return
        }
        loader.setAttribute('aria-hidden', 'true')
        loader.style.display = 'none'
    }
}