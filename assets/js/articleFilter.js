import { debounce } from 'lodash';
// install -> import
import { Flipper, spring } from 'flip-toolkit';

/**
 * class filter for search article in ajax
 * 
 * @property {HTMLElement} content - the list of the article on the page
 * @property {HTMLFormElement} form - the form for filter article
 * @property {HTMLElement} count - the number of items on the page
 * @property {HTMLElement} sorting - the button for sorting the results
 * @property {HTMLElement} pagination - the links for switch page for the search
 * @property {number} page - the number of the actual page
 * @property {bool} moreNav - if the navigation it's with more button or navigation with pagination 
 */
export class Filter {
    //instancier un class constructor
    //element -> element html
    /**
     * constructor of the class filter 
     * @param {HTMLElement} element 
     * @returns 
     */
    constructor(element) {
        if (!element) {
            return;
        }
        // la liste de mes articles 
        // this -> $this en php
        this.content = element.querySelector('.js-filter-content');
        this.form = element.querySelector('.js-filter-form');
        this.count = element.querySelector('.js-filter-count');
        this.sorting = element.querySelector('.js-filter-sorting');
        this.pagination = element.querySelector('.js-filter-pagination');
         // convertir un chaine de caractères  en entier => 转换字符串成整数
        //window.location.search -> paramatere 'GET'
        this.page = parseInt(new URLSearchParams(window.location.search).get('page') || 1);
        // js va ecuter comparation pag === 1 ou pas return true /false 
        // fini ici -> va aller a articlecontroller
        this.moreNav = this.page === 1;
        // ajout tout comportement à l'exterieux de ton function 
        this.bindEvents()

       
    }

    /**
     * add the action and the listener on HTMLElement
     */
    bindEvents() {
        const  linkClickListener = async (e, scrollToTop) => {
            e.preventDefault();

            if (e.target.getAttribute('disabled')) {
                let url;
                // e.target.tagName -> name de element
                // 如果点击的是balise I 和 span -> 他就去查找往上的parent的a链接，如果不是i就直接展现a的链接
                if (e.target.tagName === 'I' || e.target.tagName === 'SPAN') {
                    // closest-> parent de plus proche de cette element-> des que il a trouve element de 'direction' 
                    // recuperer le lien 'a'
                    url = e.target.closest('[direction]').href;
                } else {
                    url = e.target.href;
                }
                // envoyer a url a ajax
                await this.loadUrl(url);
                if (scrollToTop) {
                    window.scrollTo(0, 0);
                }
            }
           
        }

        // vient de en bas de loadUrl de function 
        this.sorting.addEventListener('click', linkClickListener);

        if (this.moreNav) {
            this.pagination.innerHTML = `<div class="text-center">
                                             <button class="btn btn-primary mt-2">Voir plus</button>
                                         </div>`;
            this.pagination.querySelector('button').addEventListener('click', this.loadMore.bind(this));
        } else {
            this.pagination.addEventListener('click', (e) => {
                linkClickListener(e, true);
            });
        }
       

        // just formulaire->type=text
        this.form.querySelectorAll('input[type="text"]').forEach(input => {
            // quand lache le touche clavier -> keyup
            // etudier formulaire -> loadForm;-> force ecuter -> chercher et envoi toute la class 
            // -> object / -> combien de temps va ecuter 
            input.addEventListener('keyup', debounce(this.loadForm.bind(this), 400));
        });

        this.form.querySelectorAll('input[type="checkbox"]').forEach(input => {
            input.addEventListener('change', debounce(this.loadForm.bind(this), 1000))
        })

        // ajout dans le button renitialisation 
        this.form.querySelector('#btn-reset-form').addEventListener('click', this.resetForm);
    }

    
    /**
     * send ajax request with url
     * @param {URL} url - the url of the ajax request  
     */
    async loadUrl(url, append=false) {
        // 在开始搜索的时候打开这个白色的透明back显示成正在查找的状态
        this.showLoader();
        // split-> decouper le chaine de caractere
        // console.error(url.split('?'));
        // chaine de caractere dans le -> URLSearchParams
        // 如果他切断了一段url之后如果1找不到或者一段是空的
        const urlParams = new URLSearchParams(url.split('?')[1] || '');
        // ajouter le ajax = true (a paramae a GET)
        urlParams.set('ajax', true);

        // 问号之前是http里的内容， 问号之后是 切段之后跟添加了ajax=true
        // return -> https://localhost:8000/articles?title=&tags%5B0%5D=6&sort=a.createdAt&direction=desc&page=1&ajax=true
        const response = await fetch(url.split('?')[0] + '?' + urlParams.toString());

        // 需要去articleController里去添加关于ajax的内容->之后会回复所有关于content的内容
        // console.error(await response.json());

        // si y a ajax ou pas 
        if (response.status >= 200 && response.status < 300) {
            // insctanse dans le data les responses-> just json
            const data = await response.json();

            // 这里回应的就是最终的刷新的内容
            // return sorting et content
            // ajout et prepare tout les components
            this.sorting.innerHTML = data.sorting;

            // modifier connu-> ajout ou remplace
            this.animationContent(data.content, append);

            // si on n'a pas le button => voir plus
            // toujours y a le button => voir plus
            if (!this.moreNav) {
                this.pagination.innerHTML = data.pagination;
            } else if (this.page === data.totalPage){
                this.pagination.style.display = 'none';
            } else {
                // remis le button => block
                this.pagination.style.display = 'block';
            }

            
            this.count.innerHTML = data.count;
           

            // url sans ajax
            urlParams.delete('ajax');

            history.replaceState({}, "", url.split('?')[0] + '?' + urlParams.toString());

            // 最后结束了搜索关闭上
            this.hidenLoader();
        }
    }


    /**
     * get all inputs of the form and send ajax request with value 
     */
    async loadForm() {
        this.page = 1;
        // instancier formdata-> tableau 
        const data = new FormData(this.form);
        // url de navigateur
        const url = new URL(this.form.getAttribute('action') || window.location.href);
        const urlParams = new URLSearchParams();
        
        data.forEach((value, key) => {
            // ajout une cle et une valeur en chaine de caractère
            urlParams.append(key, value);
        });

        // debut de url -> http
        return this.loadUrl(url. pathname + '?' + urlParams.toString());
    }

    async resetForm() {
        const url = new URL(this.form.getAttribute('action') || window.location.href);
        this.form.querySelectorAll('input').forEach(input => {
            if (input.type === 'checkbox') {
                input.checked = false;
            } else {
                input.value = '';
            }
        });
        return this.loaderUrl(url.phathname.split('?')[0]);
    }

    /**
     * load the next page with the more nav button
     */
    async loadMore() {
        // trouve le button
        const button = this.pagination.querySelector('button');
        // set button => desactive
        button.setAttribute('disabled', true);
        // envoie page 2 a partir 
        this.page++;
        const url = new URL(window.location.href);
        // tout les parameters si y'en a 
        const urlParams = new URLSearchParams(url.search);
        // set numero de page suivant 
        urlParams.set('page', this.page);

        // envoie ajax  => 2 parametres -> true => 
        await this.loadUrl(url.pathname + '?' + urlParams.toString(), true);

        //禁止affiche的button 换成remove
        button.removeAttribute('disabled');
    }


    /**
     * add animation for update content
     * @param {string} newContent - string with html code of the new liste of article
     * @param {bool} append - if replace the content or append the existing content on the page 
     * 
     */
    animationContent(newContent, append) {
        // configuration a animation
        const springName = 'veryGentle';

        //exitAnimation-> card sort
        const exitAnimation = (element, index, onComplete) => {
            // import en haut 
            spring({
                // principe de animations resort ou pas 
                // config: 'stiff',
                values: {
                    // debut et fin -> monter et descendre 
                    translateY: [0, -20],
                    opacity: [1, 0]
                },
                // flipper etudier values 
                onUpdate: ({ translateY, opacity }) => {
                    element.style.opacity = opacity;
                    // passe le temp de translate px
                    element.style.transform = `translateY(${translateY}px)`;
                },
                // animation fini-> 最后完成需要做的事情，目前先这样
                onComplete
            });
        }

        const appearAnimation = (element, index) => {
            // function spring
            spring({
                values: {
                    translateY: [-20, 0],
                    opacity: [0, 1]
                },
                onUpdate: ({ translateY, opacity }) => {
                    element.style.opacity = opacity;
                    element.style.transform = `translateY(${translateY}px`;
                },
                delay: index * 10,
            });
        }


        const flipper = new Flipper({
            element: this.content,
        });

        // tout les enfants de la liste -> quand il'est un parent ->acutellement sur le page 
        let articleCards = this.content.children;
        for (let card of articleCards) {
            flipper.addFlipped({
                element: card,
                // ici id vient de dossier _articleCard.html.twig 
                flipId: card.id,
                // juste les cards bouge 
                spring: springName,
                onExit: exitAnimation,
            });
        }

        flipper.recordBeforeUpdate();

        if(append){
            this.content.innerHTML += newContent;
        } else {
            this.content.innerHTML = newContent;
        }

        articleCards = this.content.children;
        for (let card of articleCards) { 
            flipper.addFlipped({
                element: card,
                flipId: card.id,
                spring: springName,
                onAppear: appearAnimation,
            })
        }

        flipper.update();
    } 
    /**
     * show the loader of the form
     */
    // affiche 
    showLoader() {
        this.form.classList.add('is-loading');
        const loader = this.form.querySelector('.js-loading');
        loader.style.display = 'block';
        loader.setAttribute('aria-hidden', 'false');
        }

    // plus affiche
    /**
     * hidde the loader for the form
     */
    hidenLoader() {
        this.form.classList.remove('is-loading');
        const loader = this.form.querySelector('.js-loading');
        loader.style.display = 'none';
        loader.setAttribute('aria-hidden', 'true');
    }
}