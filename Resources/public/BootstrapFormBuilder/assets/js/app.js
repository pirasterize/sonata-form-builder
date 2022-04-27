define('jquery', [], function() {
    return jQuery;
});
define('bootstrap', ["jquery"], function() {
});

define([
       "jquery",
       "collections/snippets",
       "collections/my-form-snippets", 
       "views/tab",
       "views/my-form",
       "text!data/input.json", "text!data/radio.json", "text!data/select.json", "text!data/button.json", "text!data/other.json",
       "text!templates/app/render.html",  "text!templates/app/renderjson.html",
], function(
  $, 
  SnippetsCollection, 
  MyFormSnippetsCollection,
  TabView,
  MyFormView,
  inputJSON, radioJSON, selectJSON, buttonJSON, otherJSON,
  renderTab, jsonTab
){
  return {
    initialize: function(){ 
    	new TabView({
    		title: "Input",
    		collection: new SnippetsCollection(JSON.parse(inputJSON)),
    	});
    	
    	new TabView({
        	title: "Radios / Checkboxes",
        	collection: new SnippetsCollection(JSON.parse(radioJSON)),
      	});
      	
      	new TabView({
        	title: "Select",
        	collection: new SnippetsCollection(JSON.parse(selectJSON)),
      	});
      	
      	new TabView({
        	title: "Buttons",
        	collection: new SnippetsCollection(JSON.parse(buttonJSON)),
      	});

        new TabView({
            title: "Other",
            collection: new SnippetsCollection(JSON.parse(otherJSON)),
        });
      	
      	new TabView({
	        title: "",
	        content: renderTab
	    });
      
        new TabView({
        	title: "",
        	content: jsonTab
        });
    	
    	//Make the first tab active!
	    $("#components .tab-pane").first().addClass("active");
	    $("#formtabs li").first().addClass("active");


        /* --------- customisation Andrea
         * Initialisation du formulaire à partir de la base de données
         * on recupere le champ qui formJson provenent de la BDD
         * */
        var value = $( "input[id$='json']" ).val( );

        if(value!='')
        {
            var json = $.parseJSON( value );
            var formView = new MyFormView({
                title: "Original",
                collection: new MyFormSnippetsCollection(json)
            });
        }
        else
        {
            var formView = new MyFormView({
                title: "Original",
                collection: new MyFormSnippetsCollection([
                    {   "title" : "Form Title",
                        "typefield": "formname",
                        "fields": {

                        }
                    }
                ])
            });

        }
  	}
 }
});