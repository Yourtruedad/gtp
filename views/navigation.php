<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.html">
                <img alt="Brand" src="views/images/1448935257_train.png">
            </a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="index.html">Wyszukaj</a></li>
                <li><a href="introduction.html">O nas</a></li>
                <li><a href="register.html">Załóż konto</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Dropdown <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="#">Action</a></li>
                        <li><a href="#">Another action</a></li>
                        <li><a href="#">Something else here</a></li>
                        <li role="separator" class="divider"></li>
                        <li class="dropdown-header">Nav header</li>
                        <li><a href="#">Separated link</a></li>
                        <li><a href="#">One more separated link</a></li>
                    </ul>
                  </li>
              </ul>
              <ul class="nav navbar-nav navbar-right">
                  <form class="navbar-form" action="account.html" method="post">
                      <div class="form-group">
                        <input type="text" placeholder="Email" class="form-control" name="email" maxlength="60">
                      </div>
                      <div class="form-group">
                        <input type="password" placeholder="Hasło" class="form-control" name="password" maxlength="40">
                      </div>
                      <button type="submit" class="btn btn-default form-control"><span class="glyphicon glyphicon-log-in" aria-hidden="true"></span></button>
                  </form>
            </ul>
        </div>
    </div>
</nav>