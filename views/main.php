<div id="banner" class="jumbotron">
    <div class="container">
        <h1>Hello, world!</h1>
        <p>This is a template for a simple marketing or informational website. It includes a large callout</p>
        <p><a class="btn btn-default btn-lg" href="#" role="button">Learn more &raquo;</a></p>
    </div>
</div>

<div class="container">
    <form action="search.html" method="post" class="text-center">
        <input id="mainSearchInputField" class="form-control" type="text" name="main_search" placeholder="Podaj nazwę stacji lub numer pociągu" maxlength="<?=CONFIG_ALLOWABLE_MAIN_SEARCH_LENGTH?>">
        <input id="mainSearchSubmitButton" type="submit" class="btn btn-danger btn-lg" value="Wyszukaj">
    </form>
</div>