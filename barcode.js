    var codes = "";
    var codes_el = document.getElementById('codes');
    var output_el = document.getElementById('output');

    function process_key(event) {
    var letter = event.key;
    if (letter === 'Enter') {
    event.preventDefault();
    letter = "\n";
    event.target.value = "";
}
    // match numbers and letters for barcode
    //  if (letter.match(/^[a-z0-9]$/gi)){
    if (letter.match(/^[a-z0-9\n-]$/gi)) {
    codes += letter;
}
    codes_el.value = codes;
    output_el.innerHTML = codes;
}

    function testAttribute(element, attribute) {
    var test = document.createElement(element);
    if (attribute in test) {
    return true;
} else
    return false;
}

    window.onload = function() {
    if (!testAttribute('input', 'autofocus'))
    document.getElementById('codes').focus();
    //for browser has no autofocus support, set focus to Text2.
}
