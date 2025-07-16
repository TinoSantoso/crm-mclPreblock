function formatCurrency(num)
   {
                    if (num===null)
                     num=0
                     num = num.toString().replace(/\$|\,/g, '');
                    if (isNaN(num))
                     num = "0";
                     sign = (num == (num = Math.abs(num)));
                     num = Math.floor(num * 100 + 0.50000000001);
                     cents = num % 100;
                     num = Math.floor(num / 100).toString();
                    if (cents < 10)
                     cents = "0" + cents;
                    for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3); i++)
                     num = num.substring(0, num.length - (4 * i + 3)) + ',' + num.substring(num.length - (4 * i + 3));
                    return ((sign) ? '' : '-')  + num + '.' + cents;
   }

   function formatCurrency2(num)
   {
                    if (num===null)
                     num=0
                     num = num.toString().replace(/\$|\,/g, '');
                    if (isNaN(num))
                     num = "0";
                     sign = (num == (num = Math.abs(num)));
                     num = Math.floor(num * 100 + 0.50000000001);
                     cents = num % 100;
                     num = Math.floor(num / 100).toString();
                    if (cents < 10)
                     cents = "0" + cents;
                    for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3); i++)
                     num = num.substring(0, num.length - (4 * i + 3)) + ',' + num.substring(num.length - (4 * i + 3));
                    return ((sign) ? '' : '-')  + num;
   }
   function formdate(tgl)
   {

        var mth= tgl.substring(0,2);
        var dt=tgl.substring(3,5);
        var yr=tgl.substring(6,10);
        if ((mth.trim()=="") && (yr.trim()==""))
        {
            var fd="";
        }else
        {
            var fd=mth+'/'+dt+'/'+yr;
        }



        return fd;
   }
 function formdate2(tgl)
 {
        var monthNames =
        [
          "Jan", "Feb", "Mar",
          "Apr", "May", "Jun", "Jul",
          "Aug", "Sep", "Oct",
          "Nov", "Dec"
        ];

        var date = new Date(tgl);
        var day = date.getDate();
        var monthIndex = date.getMonth();
        var year = date.getFullYear();


        return (day + ' ' + monthNames[monthIndex] + ' ' + year);
 }

 function formdate3(tgl)
 {
   // console.log('tgl : ' + tgl);
    var monthNames =
        [
          "Jan", "Feb", "Mar",
          "Apr", "May", "Jun", "Jul",
          "Aug", "Sep", "Oct",
          "Nov", "Dec"
        ];

        var mth= tgl.substring(5,7);
        var dt=tgl.substring(8,10);
        var yr=tgl.substring(0,4);
        if ((mth.trim()=="") && (yr.trim()==""))
        {
            var fd="";
        }else
        {
           // var fd=mth+'/'+dt+'/'+yr;
          // console.log('mth : ' + Number(mth));
           var fd=dt + ' ' + monthNames[Number(mth)-1] + ' ' + yr;
        }



        return fd;
 }

 function getBaseUrl()
 {
    return window.location.href.match(/^.*\//);
 }

 function message(status, msg)
 {
     if (status!="Error")
        {
             titel="<i class='fa fa-info-circle' style='color:blue;'></i> "+status;
        }else
        {
             titel="<i class='fa fa-times-circle' style='color:red;'></i> "+status;
        }
    bootbox.alert({
                     title: titel,
                   message: msg,
                  callback: function()
                            {}
                  });
 }

 function errorHandlers(xhr,status)
 {
       var err='';
       var size=0;
       var sizex=0;
       var i=0;
       var test=false;
       var error = jQuery.parseJSON(xhr.responseText);
      // for(var k in error.message)
      // {
    //     test=Array.isArray(error.message[k]);
    //   }


      // if(test)
      // {

           for(var k in error.message)
           {

                if(error.message.hasOwnProperty(k))
                {
                  if (Array.isArray(error.message[k]))
                     {
                       error.message[k].forEach(function(val){
                          err+=i+1 +'. '+val+'<br>';
                       });

                     }
                  else
                     {
                      err+=i+1 +'. '+error.message+'<br>';
                      break;
                     }
                }
                i++;
           }
               message(error.status,err);
      // }
      //else
      //{
      //         message(error.status, error.message);
    //  }
 }
