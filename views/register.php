<?php

if ($_POST) {
    $email = substr(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL), 0, CONFIG_ALLOWABLE_EMAIL_LENGTH);
    $password = substr(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING), 0, CONFIG_ALLOWABLE_PASSWORD_LENGTH);

    if (!empty($email) and !empty($password)) {
        if (false !== filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (true === $this->users->checkIfLoginNameIsFree($email)) {
                if (true === $this->users->createUserAccount($email, $password)) {
                    echo 'Account ready: ' . $email . ' ' . $password . '<br><br>';
                } else {
                    echo 'Please try again';
                }
            } else {
                echo 'User name not available';
            }
        } else {
            echo 'Invalid data';
        }
    } else {
        echo 'Empty data';
    }
}

?>

<div class="container">
    <form action="" method="post" class="text-center">
        <input id="mainSearchInputField" class="form-control" type="text" name="email" placeholder="Email" maxlength="<?=CONFIG_ALLOWABLE_EMAIL_LENGTH?>">
        <input id="mainSearchInputField" class="form-control" type="password" name="password" placeholder="Password" maxlength="<?=CONFIG_ALLOWABLE_PASSWORD_LENGTH?>">
        <input id="mainSearchSubmitButton" type="submit" class="btn btn-danger btn-lg" value="Załóż konto">
    </form>
</div>