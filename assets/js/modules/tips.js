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
}

