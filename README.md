SQLStatementBuilder
===================

PHP Sql Statement Builder for PDO Objekts.


A few examples:


```php
<?php

/* * ********************************************************* */
/* * ****************** Procedure Statement ****************** */
/* * ********************************************************* */

$procQuery = new sqlStatementBuilder('EXECUTE', 'PARAMETER');

// Set Procedure name
$procQuery->setTableOrProc('prTestProc');
$procQuery->addColumn('@Var1', false, 'tab');
$procQuery->addColumn('@Var2', false, 'fub');

$query0 = $procQuery->buildStatement();
var_dump($query0);


/* ******************************* */
/* ********** Output: ************ */
/* ******************************* */
// array 
//  'STATEMENT' => 'EXECUTE  @Var1 = ? , @Var2 = ?' 
//  'BINDS' =>  false
//  'PARAMETERS' => array
//                    0 => 'tab' 
//                    1 => 'fub'



/* * ****************************************************** */
/* * ****************** Select Statement ****************** */
/* * ****************************************************** */

$selectQuery = new sqlStatementBuilder('SELECT', 'PARAMETER');

$selectQuery->setTableOrProc('firstTable'); // Tabels and JOIN Data
$selectQuery->addJoin('SecondTable', 'PK1', 'Left Join', 'PK2', 'Column2');

$selectQuery->addColumn('ColumnName1', 'firstTable');
$selectQuery->addColumn('ColumnName2', 'firstTable');
$selectQuery->addColumn('ColumnName3', 'SecondTable');
$selectQuery->addColumn('ColumnName4', 'SecondTable');
$selectQuery->addColumn('function(5) as Column5', false);
$selectQuery->addColumn('( 5-3 ) as Column6', false);

$selectQuery->addIdentify('PKColumn', 'firstTable', '5');
$selectQuery->addIdentify('displayoff', 'firstTable', '0');

$query1 = $selectQuery->buildStatement();
var_dump($query1);


/* ******************************* */
/* ********** Output: ************ */
/* ******************************* */

//array 
//  'STATEMENT' => 'SELECT firstTable.ColumnName1 
//                              , firstTable.ColumnName2 
//                              , SecondTable.ColumnName3 
//                              , SecondTable.ColumnName4 
//                              , function(5) as Column5 
//                              , ( 5-3 ) as Column6 
//                        FROM firstTable Left Join SecondTable ON PK2.Column2 = SecondTable.PK1  
//                        WHERE firstTable.PKColumn = ?' 
//  'BINDS' => false
//  'PARAMETERS' => array
//                      0 => '5' 


/* * ****************************************************** */
/* * ****************** Insert Statement ****************** */
/* * ****************************************************** */
$insertQuery = new sqlStatementBuilder('INSERT', 'DIRECT');
$insertQuery->setTableOrProc('firstTable');

$insertQuery->addColumn('ColumnName1', 'firstTable', 'Var1');
$insertQuery->addColumn('ColumnName2', 'firstTable', 'Var2');
$insertQuery->addColumn('ColumnName3', 'firstTable', 'Var3');
$insertQuery->addColumn('ColumnName4', 'firstTable', 'Var4');

$query2 = $insertQuery->buildStatement();

var_dump($query2);

/* ******************************* */
/* ********** Output: ************ */
/* ******************************* */
// array 
//  'STATEMENT' => 'INSERT INTO firstTable(ColumnName1 , ColumnName2 , ColumnName3 , ColumnName4) 
//                               VALUES ('Var1' , 'Var2' , 'Var3' , 'Var4')' (length=120)
//  'BINDS' => false
//  'PARAMETERS' => false

/* * ****************************************************** */
/* * ****************** Update Statement ****************** */
/* * ****************************************************** */
$updateQuery = new sqlStatementBuilder('UPDATE', 'BIND');
$updateQuery->setTableOrProc('firstTable');

$updateQuery->addColumn('ColumnName1', 'firstTable', 'Var1');
$updateQuery->addColumn('ColumnName2', 'firstTable', 'Var2');
$updateQuery->addColumn('ColumnName3', 'firstTable', 'Var3');
$updateQuery->addColumn('ColumnName4', 'firstTable', 'Var4');

$updateQuery->addIdentify('PKColumn', 'firstTable', '5');
$updateQuery->addIdentify('displayoff', 'firstTable', '0');

$query3 = $updateQuery->buildStatement();
var_dump($query3);


/* ******************************* */
/* ********** Output: ************ */
/* ******************************* */
// array 
//   'STATEMENT' =>  'UPDATE firstTable SET  ColumnName1 = :colfirstTableColumnName1 
//                                         , ColumnName2 = :colfirstTableColumnName2 
//                                         , ColumnName3 = :colfirstTableColumnName3
//                                         , ColumnName4 = :colfirstTableColumnName4 
//                         WHERE PKColumn = :colfirstTablePKColumn' (length=227)
//   'BINDS' => array 
//                 ':colfirstTableColumnName1' =>  'Var1' 
//                 ':colfirstTableColumnName2' =>  'Var2'
//                 ':colfirstTableColumnName3' =>  'Var3' 
//                 ':colfirstTableColumnName4' =>  'Var4' 
//                 ':colfirstTablePKColumn' =>  '5' 
//   'PARAMETERS' =>  false



/*
   *******  PDO Example Bind ********
    $pdo = new PDO('TEST TNS', 'USERNAME', 'PASSWORT');
    $stmt = $pdo->prepare($updateQuery->getStatement());
    foreach ($updateQuery->getbinds() as $bind => $val) {
        $stmt->bindParam($bind, $val);
    }
    $stmt->execute();


   *******  PDO Example Parameter ********
    $pdo = new PDO('TEST TNS', 'USERNAME', 'PASSWORT');
    $stmt = $pdo->prepare($updateQuery->getStatement());
    $stmt->execute($updateQuery->getParameters()) ;

*/
