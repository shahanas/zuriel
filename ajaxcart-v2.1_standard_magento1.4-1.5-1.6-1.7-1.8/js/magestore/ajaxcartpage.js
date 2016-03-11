/**********Js For Ajaxcart In Cart Page**********/

var Ajaxcartpage = Class.create();
Ajaxcartpage.prototype = {
    allowFinish: true,
    initialize: function(ajaxMask,ajaxPopup,popupContent,cartPage,links, preLoadAjax){
        this.ajaxMask = ajaxMask;
        this.ajaxPopup = ajaxPopup;
        this.popupContent = popupContent;
        this.cartPage = cartPage;
        this.links = links;
		
        this.preLoadAjax = preLoadAjax;
        
        this.jsSource = [];
        this.jsCache = [];
        this.jsCount = 0;		
        this.intervalCache = 0;
		
        this.ajaxOnComplete = this.ajaxOnComplete.bindAsEventListener(this);
        this.addJsSource = this.addJsSource.bindAsEventListener(this);
        this.updateJscartEvent = this.updateJscartEvent.bindAsEventListener(this);
    },
    getCartPage: function(){
        if (!this.objCartPage){
            if ($$(this.cartPage).first()){
                this.objCartPage = $$(this.cartPage).first();
            }
        }
        return this.objCartPage;
    },
    addToCartHandle: function(requestUrl, params){
        this.url = requestUrl;
        if(window.location.href.match('https://') && !requestUrl.match('https://'))
            requestUrl = requestUrl.replace('http://','https://');
        if(!window.location.href.match('https://') && requestUrl.match('https://'))
            requestUrl = requestUrl.replace('https://','http://');
        if (requestUrl.indexOf('?') != -1)
            requestUrl += '&isajaxcart=true';
        else
            requestUrl += '?isajaxcart=true';
        if (this.getCartPage())
            requestUrl += '&isajaxcartpage=1'
        if (this.links)
            requestUrl += '&ajaxlinks=1';
		
        // $(this.ajaxMask).show();
        this.responseCache = '';
        this.requestAjax = new Ajax.Request(requestUrl,{
            method: 'post',
            postBody: params,
            parameters: params,
            onException: function (xhr, e){
                $(this.ajaxMask).hide();
                $(this.ajaxPopup).hide();
                window.location.href = this.url;
            },
            onComplete: this.ajaxOnComplete
        });
    },
    cancelRequest: function() {
        if (typeof this.requestAjax == 'object') {
            this.requestAjax.transport.abort();
        }
    },
    ajaxOnComplete: function(xhr){
        if (xhr.responseText.isJSON()){
            var response = xhr.responseText.evalJSON();
            if (response.hasOptions) {
                if (response.redirectUrl) this.addToCartHandle(response.redirectUrl,'');
                else this.popupContentWindow(response);
            } else {
                if (this.allowFinish) {
                        this.addToCartFinish(response);
                    } else {
                        this.responseCache = response;
                    }
            }
        } else {
            $(this.ajaxMask).hide();
            $(this.ajaxPopup).hide();
            window.location.href = this.url;
        }
    },
    addToCartFinish: function(response){
        if (this.getCartPage() && response.cartPage){
            if (response.emptyCart){
                this.getCartPage().update(response.cartPage);
            } else {
                $(this.popupContent).innerHTML = response.cartPage;
                ajaxcartUpdateCartHtml(this.getCartPage(),$(this.popupContent));
                $(this.popupContent).innerHTML = '';
                this.updateJscartEvent();
            }
            if (typeof truncateOptions == 'function') {
                truncateOptions();
            }
        }
        if (this.links && response.ajaxlinks){
            this.links.update(response.ajaxlinks);
            this.links.innerHTML = this.links.firstChild.innerHTML;
        }
        $(this.ajaxMask).hide();
        $(this.ajaxPopup).hide();
    },
    popupContentWindow: function(response){
        if (response.optionjs && !this.preLoadAjax){
            for (var i=0;i<response.optionjs.length;i++){
                var pattern = 'script[src="'+response.optionjs[i]+'"]';
                if ($$(pattern).first()) continue;
                this.jsSource[this.jsSource.length] = response.optionjs[i];
            }
        }
        if (response.optionhtml){
//            $(this.popupContent).innerHTML += response.optionhtml;
//            this.jsCache = response.optionhtml.extractScripts();
            pContent = $(this.popupContent);
            if (pContent.down('form')) {
                pContent.removeChild(pContent.down('form'));
            }
            pContent.innerHTML += response.optionhtml;
            if (typeof ajaxcartTemplateJs != 'undefined') ajaxcartTemplateJs();
            this.jsCache = response.optionhtml.extractScripts();
        }
        if (this.preLoadAjax) {
            this.addJsSource();
        } else {
            this.intervalCache = setInterval(this.addJsSource,500);
            this.addJsSource();
        }
    },
    addJsSource: function(){
        if (this.jsCount == this.jsSource.length){
            this.jsSource = [];
            this.jsCount = 0;
            clearInterval(this.intervalCache);
            this.addJsScript();
        } else {
            var headDoc = $$('head').first();
            var jsElement = new Element('script');
            jsElement.src = this.jsSource[this.jsCount];
            headDoc.appendChild(jsElement);
            this.jsCount++;
        }
    },
    addJsScript: function(){
        if (this.jsCache.length == 0) return false;
        try {
            for (var i=0;i<this.jsCache.length;i++){
                var script = this.jsCache[i];
                var headDoc = $$('head').first();
                var jsElement = new Element('script');
                jsElement.type = 'text/javascript';
                jsElement.text = script;
                headDoc.appendChild(jsElement);
            }
            this.jsCache = [];
            $(this.ajaxMask).hide();
            $(this.ajaxPopup).show();
            var content = $(this.popupContent);
            this.updatePopupBox(content);
            ajaxMoreTemplateJs();
        } catch (e){}
    },
    updateJscartEvent: function(){
        ajaxUpdateFormAction();
        if($('p_w') && $('p_w').value) {
            updatewithajaxcart();
        }
    },
    updatePopupBox: function(content) {
        content.style.removeProperty ? content.style.removeProperty('top') : content.style.removeAttribute('top');
        if (content.offsetHeight + content.offsetTop > document.viewport.getHeight() - 30){
            content.style.position = 'absolute';
            content.style.top = document.viewport.getScrollOffsets()[1]+10+'px';
        }else{
            content.style.position = 'fixed';
        }
        if (content.up('.ajaxcart')) {
            content.up('.ajaxcart').style.width = content.getWidth()+'px';
        }
    }
}


var AjaxcartComparePage = Class.create();
AjaxcartComparePage.prototype = {
    initialize: function(ajaxMask,ajaxPopup,popupContent,messageTag,miniCompare,links,instanceName, preLoadAjax){
        this.ajaxMask = ajaxMask;
        this.ajaxPopup = ajaxPopup;
        this.popupContent = popupContent;
		
        this.messageTag = messageTag;
        this.objMessageTag = false;
        this.miniCompare = miniCompare;
        this.objMiniCompare = false;
		
        this.links = links;
        this.instanceName = instanceName;
        this.preLoadAjax = preLoadAjax;
		
        this.jsSource = [];
        this.jsCache = [];
        this.jsCount = 0;		
        this.intervalCache = 0;
		
        this.ajaxOnComplete = this.ajaxOnComplete.bindAsEventListener(this);
        this.addJsSource = this.addJsSource.bindAsEventListener(this);
    },
    getMessageTag: function(){
        if (!this.objMessageTag){
            if ($$(this.messageTag).first()){
                this.objMessageTag = $$(this.messageTag).first();
            }
        }
        return this.objMessageTag;
    },
    getMiniCompare: function(){
        if (!this.objMiniCompare){
            if ($$(this.miniCompare).first()){
                this.objMiniCompare = $$(this.miniCompare);
            }
        }
        return this.objMiniCompare;
    },
    addToCompareHandle: function(requestUrl, params){
       
        this.url = requestUrl;
        if(window.location.href.match('https://') && !requestUrl.match('https://'))
            requestUrl = requestUrl.replace('http://','https://');
        if(!window.location.href.match('https://') && requestUrl.match('https://'))
            requestUrl = requestUrl.replace('https://','http://');
        if (requestUrl.indexOf('?') != -1)
            requestUrl += '&isajaxcart=true';
        else
            requestUrl += '?isajaxcart=true';
        if (this.getMessageTag())
            requestUrl += '&groupmessage=1';
        if (this.getMiniCompare())
            requestUrl += '&minicompare=1';
        if (this.links)
            requestUrl += '&ajaxlinks=1';
        this.requestAjax = new Ajax.Request(requestUrl,{    
            method: 'post',
            postBody: params,
            parameters: params,
            onException: function (xhr, e){
                $(this.ajaxMask).hide();
                $(this.ajaxPopup).hide();
                window.location.href = this.url;
            },
            onComplete: this.ajaxOnComplete
        });
    },
    cancelRequest: function() {
        if (typeof this.requestAjax == 'object') {
            this.requestAjax.transport.abort();
        }
    },
    ajaxOnComplete: function(xhr){
        if (this.requestAjax.getStatus()) {  
            if (xhr.responseText.isJSON()){
                var response = xhr.responseText.evalJSON();
                this.addToCartFinish(response);
            } else {
                $(this.ajaxMask).hide();
                $(this.ajaxPopup).hide();
                window.location.href = this.url;
            }
        }
    },
    addToCartFinish: function(response){
        if (this.getMessageTag() && response.message){       
            this.getMessageTag().update(response.message);
            this.getMessageTag().innerHTML = this.getMessageTag().firstChild.innerHTML;
        }
        if (this.getMiniCompare() && response.miniCompare){
            this.getMiniCompare().each(function(mnc){
                mnc.update(response.miniCompare);
                mnc.innerHTML = mnc.firstChild.innerHTML;
            });
        }
        if (typeof truncateOptions == 'function') {
            truncateOptions();
        }
        if (this.links && response.ajaxlinks){
       
            this.links.update(response.ajaxlinks);
            this.links.innerHTML = this.links.firstChild.innerHTML;
        }
        $(this.ajaxMask).hide();
        var instanceName = this.instanceName;
        if(instanceName == 'compare'){
            ajaxCartHideComparebyTimout(response);
        }else if(instanceName == 'wishlist'){
            ajaxCartHideWishlistbyTimout(response);
        }
    },
    popupContentWindow: function(response){
        if (response.optionjs && !this.preLoadAjax){
            for (var i=0;i<response.optionjs.length;i++){
                var pattern = 'script[src="'+response.optionjs[i]+'"]';
                if ($$(pattern).first()) continue;
                this.jsSource[this.jsSource.length] = response.optionjs[i];
            }
        }
        
        if (this.preLoadAjax) {
            this.addJsSource();
        } else {
            this.intervalCache = setInterval(this.addJsSource,500);
            this.addJsSource();
        }
    },
    addJsSource: function(){
        if (this.jsCount == this.jsSource.length){
            this.jsSource = [];
            this.jsCount = 0;
            clearInterval(this.intervalCache);
            this.addJsScript();
        } else {
            var headDoc = $$('head').first();
            var jsElement = new Element('script');
            jsElement.src = this.jsSource[this.jsCount];
            headDoc.appendChild(jsElement);
            this.jsCount++;
        }
    },
    addJsScript: function(){
        if (this.jsCache.length == 0) return false;
        try {
            for (var i=0;i<this.jsCache.length;i++){
                var script = this.jsCache[i];
                var headDoc = $$('head').first();
                var jsElement = new Element('script');
                jsElement.type = 'text/javascript';
                jsElement.text = script;
                headDoc.appendChild(jsElement);
            }
            this.jsCache = [];
            $(this.ajaxMask).hide();
            $(this.ajaxPopup).show();
            var content = $(this.popupContent);
            this.updatePopupBox(content);
            ajaxMoreTemplateJs();
        } catch (e){}
    },
    updatePopupBox: function(content) {
        content.style.removeProperty ? content.style.removeProperty('top') : content.style.removeAttribute('top');
        if (content.offsetHeight + content.offsetTop > document.viewport.getHeight() - 30){
            content.style.position = 'absolute';
            content.style.top = document.viewport.getScrollOffsets()[1]+10+'px';
        }else{
            content.style.position = 'fixed';
        }
        if (content.up('.ajaxcart')) {
            content.up('.ajaxcart').style.width = content.getWidth()+'px';
        }
    }
}