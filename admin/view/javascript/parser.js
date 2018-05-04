var startBtn = document.querySelector('[data-action="parse"]');

$(startBtn).on('click', function() {
	var query = $.ajax('http://lena-basco.ru/catalog-news/');
	
	query.done(function (data) {
        analysisSite(data);
    });
	
	query.fail(function (e, g, f) {
        console.log('Epic Fail');
		console.log(e);
    });
});

function analysisSite(data){
    var res = '';
    var list = $(data).find('.catalog_all_list');
	console.log(list);

      /*list.find('.catalog_list_one:first').each(function(){
        var image = $(this).find('img:first').attr('src');
        var link = $(this).find('.slide_title > a').attr('href');
        
        $.ajax('http://lena-basco.ru' + link)
        .done(function (page) {
            analizePage($(page).find('.content'));
        })
        .fail(function(e) {
            console.log('Fail on Links');
        });
        
//        res += 'img: ' + image + ' - title: ' + $(this).find('.slide_title').text() + ';\n';
      });*/

//      $('#resultbox').html(res);
}
