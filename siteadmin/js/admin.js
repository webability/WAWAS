/* Javascript for the administration system of WAWAS
   Autor: Phil, 2015-12-07
   
   Changes:
*/

var WAWAS = { version: '1.0.0',
              devel: false,
              recon_time: 100 // fetch info every 1 sec

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

WAWAS.forhuman = function(i)
{
  if (i < 1000)
    return i;
  if (i < 1000000)
    return (Math.round(i/10)/100) + 'kB';
  if (i < 1000000000)
    return (Math.round(i/10000)/100) + 'MB';
  if (i < 1000000000000)
    return (Math.round(i/10000000)/100) + 'GB';
  return (Math.round(i/10000000000)/100) + 'TB';
}

WAWAS.timeforhuman = function(i)
{
  if (i < 1)
    return Math.round(i*1000) + 'ms';
  if (i < 60)
    return (Math.round(i*10)/10) + 's';
  if (i < 3600)
    return (Math.round(i/6)/10) + 'm';
  return (Math.round(i/360)/10) + 'h';
}

WAWAS.builddata = function(data)
{
  WA.toDOM('numthreads').innerHTML = data.threads.length;
  WA.toDOM('counter').innerHTML = data.counter;
  WA.toDOM('counterpetitions').innerHTML = data.counterpetitions;
  WA.toDOM('load1').innerHTML = data.load[0];
  WA.toDOM('load5').innerHTML = data.load[1];
  WA.toDOM('load15').innerHTML = data.load[2];

  var time = data.time;
  var txt = '';
  for (var i = 0; i < data.threads.length; i++)
  {
    // input 
    var I = WAWAS.forhuman(data.threads[i].i);
    var O = WAWAS.forhuman(data.threads[i].o);
    var start = WAWAS.timeforhuman(time - data.threads[i].a);
    var last = WAWAS.timeforhuman(time - data.threads[i].l);
    var runtime = WAWAS.timeforhuman(data.threads[i].m);
    
    txt += '<div style="border: 1px solid #ddd; padding: 5px; margin-top: 5px;"><a href="/threads/'+data.threads[i].id+'">' + data.threads[i].id + '</a>: I/'+ I + ' - O/' + O + ' - ' + start + ' - ' + last + ' #' + data.threads[i].n + '<br />' + data.threads[i].t + ':' + data.threads[i].p + ' =&gt; ' + data.threads[i].u + ' in ' + runtime + '</div>';
  }
  WA.toDOM('listthreads').innerHTML = txt;

  txt = '';
  for (var i = 0; i < data.petitions.length; i++)
  {
    // filter local getdata petitions
//    if (data.petitions[i].indexOf("listeners/getdata") > -1)
//      continue;
    txt += '<div>' + data.petitions[i] + '</div>';
  }
  WA.toDOM('listpetitions').innerHTML = txt;
}



