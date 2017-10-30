/**
     * Show differences between two given texts
     */
    function diff(text1, text2, out) {
        var dmp = new diff_match_patch();
        var d = dmp.diff_lineMode_(text1, text2);
        /*var d = dmp.diff_main(text1, text2);*/
        dmp.diff_cleanupSemantic(d);
        var ds = dmp.diff_prettyHtml(d);
        
        var dom = $(ds);
        var html = "";
        
        dom.each(function() {
            switch(this.nodeName) {
                case "SPAN":
                    html += $(this).text();
                    break;

                case "DEL": 
                case "INS":
                    var txt = $(this).text();//debug(txt);
                    var apx = "";
                    
                    //txt = txt.replace(/"/g, "&quot;")

                    /* Cut the rest tags that should not be marked as del or ins */
                    var index = txt.search(/<[^\/.]*>?[^<>]*$/);
                    if(index > -1)
                    {
                        apx = txt.substr(index, txt.length+1);
                        // Fix broken apendix
                        if(txt.match(/<\/[a-z0-9]+$/))
                        {
                            apx = apx.replace(/(<\/[a-z0-9]+)$/, "$1>");
                        }

                        txt = txt.substr(0, index);

                        // Move del/ins text back from apx to txt
                        if(apx.match(/^<[a-z0-9]+>[^<>]+/))
                        {
                            var match = apx.match(/^(<[a-z0-9]+>[^<>]+)(.*)/);
                            
                            txt += match[1];
                            apx = match[2];
                        }
                    }
                    
                    // Fix broken html tag at the beginning
                    if(txt.match(/^[a-z0-9]+>/))
                    {
                        var rep = "<$1";
                        var reg = new RegExp("^([a-z0-9]+>)");
                        var hreg = new RegExp("<$");
                        if(txt.match(/^([0-9]+>)/))
                        {
                            rep = "<h$1";
                            reg = new RegExp("^([0-9]+>)");
                            hreg = new RegExp("<h$");
                        }
                        txt = txt.replace(reg, rep);
                        html = html.replace(hreg, "");
                    }
                   
                    /* Wrap plain text without tags into del or ins */
                    var match = txt.match(/^[^<]*/); // ^[^<\/]*
                    if(match[0].trim() != "")
                        txt = txt.replace(/^[^<]*/, '<'+this.localName+'>$&</'+this.localName+'>');
                        
                    /* Add del or ins classes to the rest open tags */
                    txt = txt.replace(/<([a-z0-9]+) *[^/]*?>/g, '<$1 class="'+this.localName+'">');

                    txt += apx;
                    html += txt;
                    break;
            }
            
            // Clean html from unnecessary tags
            html = html.replace(/[>]{2}\s?$/, "");
        });
        
        out.html(html);
    }