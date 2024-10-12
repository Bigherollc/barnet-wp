var rootCateChecked = document.evaluate("//ul[contains(@class,'children')]/parent::li/label[1]/input[@type='checkbox']", document, null, XPathResult.ANY_TYPE, null);
while (node = rootCateChecked.iterateNext()) {
    node.style.display = 'none';
}