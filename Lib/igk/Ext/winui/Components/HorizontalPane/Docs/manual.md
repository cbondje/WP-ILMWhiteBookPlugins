`Horizontal pane manual`

la méthode `addPage` permet d'ajouter une page

exemple:
```php
$pane = $page->addHorizontalPane($this); 
$pane->clearPages();
$pane->addPage()->addDiv()->Content = "Page1";
$pane->addPage()->addDiv()->Content = "Page2";
$pane->flush();
```

si nous passons un repertoire au composant unutile de faire appel à clear page et configure.
```php
$pane = $page->addHorizontalPane($this, "Data/R");
$pane->flush(); 
```

