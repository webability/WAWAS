/* Javascript for the administration system of WAWAS
   Autor: Phil, 2015-12-07
   
   Changes:
*/

var WAWAS = { version: '1.0.0',
              devel: false,
              recon_time: 1000 // fetch info every 1 sec

            };

/* Start the data loader.
   What we load depends on the page we are into
 */
WAWAS.onload = function()
{
  WebFontConfig = {
    google: { families: [ 'Source+Sans+Pro:200,300,400,600:latin' ] }
  };
  (function() {
    var wf = document.createElement('script');
    wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
      '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
    wf.type = 'text/javascript';
    wf.async = 'true';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(wf, s);
  })(); 

  WAWAS.recon();
}


WAWAS.recon = function()
{
  // Ask to the server anything new
  var request = WA.Managers.ajax.createRequest('/listeners/getdata', 'POST', null, WAWAS.getdata, true);
}

WAWAS.getdata = function(request)
{
  setTimeout(WAWAS.recon, WAWAS.recon_time);

  data = WA.JSON.decode(request.responseText);
  WAWAS.builddata(data);
}

WAWAS.builddata = function(data)
{
  WA.toDOM('numthreads').innerHTML = data.threads.length;
  var txt = '';
  for (var i = 0; i < data.threads.length; i++)
    txt += '<a href="/threads/'+data.threads[i].id+'">' + data.threads[i].id + '</a>: I/'+data.threads[i].input+ ' - O/' + data.threads[i].output + '<br />';
  WA.toDOM('listthreads').innerHTML = txt;
}


