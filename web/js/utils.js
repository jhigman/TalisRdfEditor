function redirect(url)
{
    document.location.href = url;
}

function showModalLayer(url, w, h)
{
	var msg;
	var url;
	
	if (!w) w = 500;
	if (!h) h = 300;
	
	if (urlOrMsg)
	{
		msg = 'Loading...';
	}
	
    $$('body')[0].insert({top: '<div id="modalLayer"></div>'});
    
    new Ajax.Updater('modalPane', url, 
	{
    	method: 'get',
    	evalScripts: true,
    	onFailure: function(transport) {
    		alert('Error getting page to load into modal pane');
    	}
	});
}

function hideModalLayer()
{
    $('modalLayer').remove();
    if ($('modalPane')) {
        $('modalPane').remove();
    }
}
