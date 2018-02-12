<?php

$queries = array();

$queries['actor']    = 'SELECT * FROM actor';
$queries['address']  = 'SELECT * FROM address';
$queries['category'] = 'SELECT * FROM category';

$queries['actor-keys'] = 'SELECT actor_id as value FROM actor';

$queries['actor:byId'] = 'SELECT * FROM actor WHERE actor_id IN (@KEY)';


//$queries['actor:create-table']   = 'SHOW CREATE TABLE actor';
//$queries['address:create-table'] = 'SHOW CREATE TABLE address';

$queries['film_text'] = 'SELECT * FROM film_text';

$queries['insert_film_text'] = "INSERT INTO film_text (film_id,title,description) VALUES (2000,\"ZORRO ARK\",\"A Intrepid Panorama of a Mad Scientist And a Boy who must Redeem a Boy in A Monastery\");";

$queries['insert_film_text_param_id'] = "INSERT INTO film_text (film_id,title,description) VALUES (@KEY,\"ZORRO ARK\",\"A Intrepid Panorama of a Mad Scientist And a Boy who must Redeem a Boy in A Monastery\");";