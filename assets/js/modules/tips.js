export default class Tips {
    static iconBar() {
        $('#header__icon').click(function(e){
            e.preventDefault()
            $('nav').toggleClass('active')
        })
    }
    static replyComment() {
        $('.reply').click(function(e) {
            e.preventDefault()
            let $form = $('#form-comment')
            let $this = $(this)
            let parent_id = $this.data('id')
            let parent = $this.data('parent')
            let $comment = $('#comment-' + parent)
            $('#comment_parent_id').val(parent_id)
            $form.hide();
            $comment.after($form)
            $form.slideDown()
        })
    }
    static comment() {
        $('.post-comment').click(function(e) {
            e.preventDefault()
            let $form = $('#form-comment')
            let $this = $(this)
            $form.hide();
            $this.after($form)
            $form.slideDown()
        })
    }
    static copyLink() {
        $('.copy-link').click(function(e) {
            e.preventDefault()
            let link = this.getAttribute('href')
            let temp = $("<input>")
            $("body").append(temp);
            temp.val(link).select()
            document.execCommand("copy");
            temp.remove()
        })
    }
    static responsiveEmbed() {
        $('iframe').parent().addClass('embed-responsive embed-responsive-16by9')
    }
    static toggleAuthor() {
        $('.toggle-item').click(function(e){
            e.preventDefault()
            $('.cat-high-toggle a').removeClass('cat-high-selected')
            $(this).addClass('cat-high-selected')
        })
    }
    static socialButtons() {

        var popupCenter = function(url, title, width, height){
            var popupWidth = width || 640;
            var popupHeight = height || 320;
            var windowLeft = window.screenLeft || window.screenX;
            var windowTop = window.screenTop || window.screenY;
            var windowWidth = window.innerWidth || document.documentElement.clientWidth;
            var windowHeight = window.innerHeight || document.documentElement.clientHeight;
            var popupLeft = windowLeft + windowWidth / 2 - popupWidth / 2 ;
            var popupTop = windowTop + windowHeight / 2 - popupHeight / 2;
            var popup = window.open(url, title, 'scrollbars=yes, width=' + popupWidth + ', height=' + popupHeight + ', top=' + popupTop + ', left=' + popupLeft);
            popup.focus();
            return true;
        };
        
        document.querySelector('.share_twitter').addEventListener('click', function(e){
            e.preventDefault();
            // var url = this.getAttribute('data-url');
            var url = $(location).attr("href");
            var shareUrl = "https://twitter.com/intent/tweet?text=" + encodeURIComponent(document.title) +
                "&via=Bouclier_Joseph_com" +
                "&url=" + encodeURIComponent(url);
            popupCenter(shareUrl, "Partager sur Twitter");
        });
    
        document.querySelector('.share_facebook').addEventListener('click', function(e){
            e.preventDefault();
            var url = $(location).attr("href");
            // var url = this.getAttribute('data-url');
            var shareUrl = "https://www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(url);
            popupCenter(shareUrl, "Partager sur facebook");
        });
    
        document.querySelector('.share_whatsapp').addEventListener('click', function(e){
            e.preventDefault();
            // var url = this.getAttribute('data-url');
            var url = $(location).attr("href");
            var shareUrl = "https://api.whatsapp.com/send?text=" + encodeURIComponent(url);
            popupCenter(shareUrl, "Partager sur Whatsapp");
        });
    }
}

