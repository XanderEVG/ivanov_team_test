$( document ).ready(function() {
    function setLike(movie_id) {
        var setLikeQuery = $.post(
            "setlike",
            {
                movie_id: movie_id
            },
            setLikeQuerySuccess
        );

        setLikeQuery.fail(function(data) {
            console.log('fail: ', data);
        });
    }

    function setLikeQuerySuccess(data) {
        let rez = JSON.parse(data);
        if (rez.result == true) {
            let movie_id = rez.movie_id;
            let likes_count = parseInt($('.movie.' + movie_id + ' .likes_val').html());
            $('.movie.' + movie_id + ' .likes_val').html(likes_count + 1);
            $('.movie.' + movie_id + ' .like').addClass('liked');
        } else {
            console.log('fail: ', rez.msg);
        }
    }

    $('.like').click(function (){
        let movie_id = $(this).attr('data-movie-id');
        setLike(movie_id);
    });
});