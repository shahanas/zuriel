var jq = jQuery.noConflict();
function checkDelivery()
{
    event.preventDefault();
    var pincode = jq('#pincode').val();
    jq('.pincode-validation .message').removeClass('available');
    jq('.pincode-validation .message').removeClass('not-available');
    jq('.pincode-validation .message').html("Check delivery availability");
    jq('#pincode-submit').attr("disabled", "disabled");
    if(pincode)
    {
        if(pincode.length!=6)
        {
            jq('.pincode-validation .message').removeClass('available');
            jq('.pincode-validation .message').removeClass('not-available');
            jq('.pincode-validation .message').html("Invalid Pincode");
            jq('#pincode-submit').attr("disabled", false);
            return;
        }
        var url =  jq('#pincode-submit').attr('data-url');
        jq('.pincode-validation .loader').show();
        new Ajax.Request(url, {
            method: 'Post',
            parameters: {"dest_zip":pincode},
            onComplete: function(transport) {
                response=transport.responseText;
                if(response=='1')
                {
                    var msg="Delivery Available";
                    jq('.pincode-validation .message').html(msg);
                    jq('.pincode-validation .message').removeClass('not-available');
                    jq('.pincode-validation .message').addClass('available');
                    jq('#pincode-submit').attr("disabled", false);
                }
                else if(response=='0')
                {
                    var msg="Delivery Not Available";
                    jq('.pincode-validation .message').html(msg);
                    jq('.pincode-validation .message').removeClass('available');
                    jq('.pincode-validation .message').addClass('not-available');
                    jq('#pincode-submit').attr("disabled", false);
                }
                else
                {
                    var msg="Something went wrong";
                    jq('.pincode-validation .message').html(msg);
                    jq('.pincode-validation .message').removeClass('available');
                    jq('.pincode-validation .message').removeClass('not-available');
                    jq('#pincode-submit').attr("disabled", false);
                }
            }
        });
    }
    else
    {
        jq('.pincode-validation .message').removeClass('available');
        jq('.pincode-validation .message').removeClass('not-available');
        jq('.pincode-validation .message').html("Check delivery availability");
        jq('#pincode-submit').attr("disabled", false);
    }
}
jq(document).ready(function(){
   jq('#pincode').on('click',function(){
       jq('.pincode-validation .message').removeClass('available');
       jq('.pincode-validation .message').removeClass('not-available');
       jq('.pincode-validation .message').html("Check delivery availability");
   });
});