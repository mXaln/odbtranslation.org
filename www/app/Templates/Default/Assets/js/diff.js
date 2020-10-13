/**
 * Show differences between two given texts
 */
function diff_plain(text1, text2, out) {
    const dmp = new diff_match_patch();
    const d = dmp.diff_lineMode_(text1, text2);
    /*var d = dmp.diff_main(text1, text2);*/
    dmp.diff_cleanupSemantic(d);
    const ds = dmp.diff_prettyHtml(d);
    out.html(ds);
}

function htmlToText(html) {
    const re = /<([^ >]+)[^>]*>(.*?)<\/\1>|<[^\/]+\/>/g;
    const blocks = ["div","p","h1","h2","h3","h4","h5","h6","ul","ol","li","dl","pre","hr","blockquote","address"];

    html = html.replace(re, function(m, tag, text) {
        let res = "";
        if(blocks.indexOf(tag) > -1) {
            res += "\n\n";
        }
        res += text + " ";
        return res;
    });
    return html.trim();
}