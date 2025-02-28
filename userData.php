<?php
    function userData(){
        require_once('database_local.php');
        $db = getDB();
        $email = $_SESSION['email'];
        $queryCategory = 'SELECT * FROM powerBankManagers
            WHERE emailAddress = :category_id';
        $statement1 = $db->prepare($queryCategory);
        $statement1->bindValue(':category_id', $email);
        $statement1->execute();
        $category = $statement1->fetch();
        $first_name = $category['firstName'];
        $last_name = $category['lastName'];
        $statement1->closeCursor();
        echo "Welcome ";
        echo $first_name;
        echo " ";
        echo $last_name;
        echo " (";
        echo $email;
        echo ")";
    }
?>