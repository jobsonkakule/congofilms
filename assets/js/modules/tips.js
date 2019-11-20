export default class Tips {
    static iconBar() {
        $('#header__icon').click(function(e){
            e.preventDefault()
            $('nav').toggleClass('active')
        })
    }
}