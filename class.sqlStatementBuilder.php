<?php


/*
 * Copyright (C) 2014 Benedikt Wurz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Create Sql Statement
 *
 * @author Benedikt Wurz
 * @category SQL ,Statement, Query ,Builder
 * @version 1.0
 */
class sqlStatementBuilder {

//put your code here
    Public $debug = false;
    public $debugcode = false;
    public $columns = false; //= array($columns => $data);
    public $identify = false;
    public $tables = array();
    public $tableorProc = false;
    public $tableString = false;
    public $defaultTable = false;
    public $command = false;
    public $binds = false;
    public $parameters = false;
    public $sqlVarVartype = false;
    public $finishQuery = false;
    public $sqlIndentityCompusition = 'AND';
    private $commandtype = array('SELECT', 'UPDATE', 'DELETE', 'INSERT', 'EXECUTE', 'CALL');
    private $operatorType = array('LIKE', 'BETWEEN', '=', '<=', '>=', '<>');

    // SQL Var Type
    const VarTypeText = 'TEXT';
    const VarTypeNummeric = 'NUMERIC';
    const VarTypeReal = 'REAL';
    const VarTypeBool = 'BOOL';
    // SQL  Type
    const SqlVarTypeParamete = 'PARAMETER';
    const SqlVarTypeDirect = 'DIRECT';
    const SqlVarTypeBind = 'BIND';

    function setSQLcompusition($compType) {
        $this->sqlIndentityCompusition = $compType;
    }

    /**
     * 
     * @param type $command
     * @param type $SqlVarType
     * @param type $debug
     */
    function __construct($command = 'SELECT', $SqlVarType = 'BIND', $debug = null) {
        // Init Debug
        /* if (is_a($debug, 'debug')) {
          $this->debug = $debug;
          } else {
          $this->debug = new debug();
          } */
        $this->debugcode = 3;

        $this->setSqlVarType($SqlVarType);

        $this->addDebugLine('LOAD "sqlBuilder" CLASS', 1);


        // set Command 
        $this->setCommand($command);
    }

    /**
     *  Debug Code
     * @param type $message
     * @param type $code
     */
    protected function addDebugLine($message, $code) {
        /* $this->debug->addDebugLine($message, $code, 'sqlStatementBuilder'); */
        $this->debug[] = $message;
        if ($code <= 0) {
            die($message);
        }
    }

    /**
     * SQL Set Var type for the Execute in the Database
     * @param type $SqlVarType
     */
    function setSqlVarType($SqlVarType) {
        $SqlVarType = strtoupper($SqlVarType);
        if (in_array($SqlVarType, array('BIND', 'DIRECT', 'PARAMETER'))) {
            $this->sqlVarVartype = $SqlVarType;
            $this->addDebugLine('Set SQLVarType as ' . $SqlVarType, 3);
        } else {
            $this->addDebugLine('Error In setSqlVarType Type "' . $SqlVarType . '" not defined', 3);
        }
    }

    /**
     * set the command or the table of the Statement
     * @param type $tableOrProg
     */
    function setTableOrProc($tableOrProg) {
        if ($this->command == 'EXECUTE' or $this->command == 'CALL') {
            $this->Proc = $tableOrProg;
        } else {
            $this->defaultTable = $tableOrProg;
            $this->tables[] = $tableOrProg;
            $this->tableString = $tableOrProg;
        }
    }

    /**
     * set the Command of the SQL Statement
     * @param type $command
     */
    private function setCommand($command) {
        $command = strtoupper($command);
        if (in_array($command, $this->commandtype) == False) {
            $this->addDebugLine('Set Command error :"' . $command . '" is not an sqlStatement Command', 0);
            return False;
        }
        $this->command = $command;

        $this->addDebugLine('Set Command to ' . $command, 4);
    }

    /**
     * add A new Join table!
     * @param type $reftable
     * @param type $refColumn
     * @param type $jointype
     * @param type $pkTable
     * @param type $pkColumn
     * @return boolean
     */
    public function addJoin($reftable, $refColumn, $jointype, $pkTable, $pkColumn) {
        if ($this->command != 'SELECT') {
            $this->addDebugLine('It´s not a SELECT Statement', 1);
            return false;
        }
        $this->tables[] = $reftable;
        $this->tableString .= " $jointype $reftable ON $pkTable.$pkColumn = $reftable.$refColumn ";
        return true;
    }

    /**
     * add a Column Lable in the Statement
     * @param type $column Column in the Database
     * @param type $data   if data is requestet
     * @param type $type   type of the Column
     */
    function addColumn($column, $table = null, $data = false, $type = 'TEXT') {
        if ($table == null and $table != false) {
            $table = $this->defaultTable;
        }

        if ($this->command == 'SELECT' /* or basefunctions::getValidVar($data, $type) */) {
            $this->columns[$column]['data'] = $data;
            $this->columns[$column]['table'] = $table;
            $this->columns[$column]['type'] = $type;
            $this->addDebugLine("add Column column = '$table.$column' Data = '$data' ", 5);
        } else {
            $this->addDebugLine("add Column error column = '$table.$column' Data = '$data' is not type = '$type'", 2);
            return false;
        }
        return true;
    }

    /**
     * Add a New identifying label
     * @param type $column
     * @param type $data
     * @param type $operator
     * @param type $not 
     * @param type $type
     */
    function addIdentify($column, $table = false, $data = null, $operator = '=', $not = False, $type = 'text') {
        //echo $operator;

        if ($table == false) {
            $table = $this->defaultTable;
        }

        if (!in_array($operator, $this->operatorType)) {
            $this->addDebugLine("Operator Error in '.$table.$column.' operator is not Valide '$operator' ", -1);
        }

        if (/* basefunctions::getValidVar($data, $type) */ true) {
            $array = array();
            $array[$column]['table'] = $table;
            $array[$column]['data'] = $data;
            $array[$column]['type'] = $type;
            $array[$column]['operator'] = $operator;
            $array[$column]['not'] = $not;
            $this->identify['SINGLE'][] = $array;
            $this->addDebugLine('add Identify column = "' . $table . '.' . $column . '" Data = "' . $data . '"', 5);
        } else {
            $this->addDebugLine('add Column error column = "' . $table . '.' . $column . '" Data = "' . $data . '" is not type = "' . $type . '" ', 2);
            return false;
        }
        return true;
    }

    /**
     * Add Bind to Execute or for Where Statement
     * @param type $column
     * @param type $table
     * @param type $data
     */
    public function addBind($bindVar, $data) {
        $bindVar = trim($bindVar);

        // $bindVar = ':' . basefunctions::getCleanString($bindVar);
        $this->binds[$bindVar] = $data;
        return $bindVar;
    }

    /**
     * Add a Addional Parameter
     * @param type $data
     */
    public function addParamenter($data) {
        $this->parameters[] = $data;
        return '?';
    }

    private function getSqlDataVar($data, $column, $table, $bindSuffix) {
        $return = '';

        // SQL variable Type definired
        if ($data != false) {

            switch ($this->sqlVarVartype) {
                case 'BIND':
                    $bindVar = ':' . $bindSuffix . $table . $column;
                    $return = $this->addBind($bindVar, $data);
                    break;

                case 'DIRECT':
                    $return = "'" . $data . "'";
                    break;

                case 'PARAMETER':
                    $return = $this->addParamenter($data);
                    break;
            }
        }

        return $return;
    }

    /**
     * get the Column names
     * @return array ($nr => array('Column' => $COLUMNNAME , 'data' => $DATA))
     */
    private function getColumns() {
        $return = array();
        $this->addDebugLine('start get Columns ', 6);

        foreach ($this->columns as $column => $value) {

            // by select is not relevante if the data is false
            if ($this->command == 'SELECT' or $value['data'] != false) {


                // if is a select get the table on it
                if ($this->command == 'SELECT' and $value['table'] != false) {
                    $array['column'] = $value['table'] . '.' . $column;
                } else {
                    $array['column'] = $column;
                }
                $array['data'] = $this->getSqlDataVar($value['data'], $column, $value['table'], 'col');
                $return[] = $array;
            } else {
                
            }
        }
        return $return;
    }

    /**
     * get all Identifyre Labels there ar aktive
     * @return type
     */
    private function getSingleIdentify() {


        $return = array();

        foreach ($this->identify['SINGLE'] as $nr => $columns) {

            foreach ($columns as $column => $value) {

                if ($value['data'] != false) {
                    // if is a select get the table on it
                    if ($this->command == 'SELECT') {
                        $array['column'] = $value['table'] . '.' . $column;
                    } else {
                        $array['column'] = $column;
                    }

                    //the SqlString Data
                    $array['data'] = $this->getSqlDataVar($value['data'], $column, $value['table'], 'col');

                    $array['operator'] = $value['operator'];


                    $return[] = $array;
                }// end if Data
            }// end Foreach column
        }// end foreach Columns
        $this->addDebugLine('Start Get Identify and get ' . count($return) . ' identifiry', 6);

        return $return;
    }

    /**
     * Get all Binds for the statement
     * @return type
     */
    public function getBinds() {
        if ($this->finishQuery == false) {
            $this->addDebugLine('Query is not Prozessed Pleass Exetcute "buildStatement" ', -1);
        }
        return $this->binds;
    }

    /**
     * get all Parameters for the statement
     * @return type
     */
    public function getParameters() {
        if ($this->finishQuery == false) {
            $this->addDebugLine('Query is not Prozessed Pleass Exetcute "buildStatement" ', -1);
        }
        return $this->parameters;
    }

    /**
     * get the Statement
     * @return type
     */
    public function getStatement() {

        if ($this->finishQuery == false) {
            $this->addDebugLine('Query is not Prozessed Pleass Exetcute "buildStatement" ', -1);
        }

        return $this->finishQuery;
    }

    /**
     * 
     */
    public function buildStatement() {

        $this->addDebugLine('START buildStatement ', 1);

        if ($this->defaultTable == false and $this->command == 'SELECT') {
            $this->addDebugLine('No Table defined', 0);
            return false;
        }

        if ($this->command == false) {
            $this->addDebugLine('No Command defined', 0);
            return false;
        }

        $statement = '';
        switch ($this->command) {

            case 'SELECT':

                $statement .= 'SELECT ';
                $columnarray = $this->getColumns();


                foreach ($columnarray as $val) {
                    $columns[] = $val['column'];
                }

                $statement .= implode(' , ', $columns);

                $statement .= ' FROM ' . $this->tableString;

                $identArray = $this->getSingleIdentify();



                foreach ($identArray as $var) {
                    $idents[] = $var['column'] . ' ' . $var['operator'] . ' ' . $var['data'];
                }

                if (count($idents) > 0) {
                    $statement .= ' WHERE ';
                    $statement .= implode(' ' . $this->sqlIndentityCompusition . ' ', $idents);
                }



                break;
            case 'EXECUTE':
            case 'EXEC':
            case 'CALL':

                $statement .= $this->command . ' ';

                $statement .= $this->tableorProc . ' ';

                $columnarray = $this->getColumns();
                foreach ($columnarray as $val) {
                    $columns[] = $val['column'] . ' = ' . $val['data'];
                }

                $statement .= implode(' , ', $columns);

                break;
            case 'UPDATE':
                $statement .= 'UPDATE ';

                $statement .= $this->defaultTable;

                $statement .= ' SET ';

                $columnarray = $this->getColumns();


                foreach ($columnarray as $val) {
                    $columns[] = $val['column'] . ' = ' . $val['data'];
                }

                $statement .= implode(' , ', $columns);


                $identArray = $this->getSingleIdentify();

                foreach ($identArray as $var) {
                    $idents[] = $var['column'] . ' ' . $var['operator'] . ' ' . $var['data'];
                }

                if (count($idents) > 0) {
                    $statement .= ' WHERE ';
                    $statement .= implode(' ' . $this->sqlIndentityCompusition . ' ', $idents);
                }
                break;

            case 'DELETE':

                $statement .= 'DELETE ';

                $statement .= $this->defaultTable;

                $statement .= '  ';



                $identArray = $this->getSingleIdentify();

                foreach ($identArray as $var) {
                    $idents[] = $var['column'] . ' ' . $var['operator'] . ' ' . $var['data'];
                }

                if (count($idents) > 0) {
                    $statement .= ' WHERE ';
                    $statement .= implode(' ' . $this->sqlIndentityCompusition . ' ', $idents);
                }
                break;


            case 'INSERT':
                $statement .= 'INSERT INTO ';

                $statement .= $this->defaultTable;


                $columnarray = $this->getColumns();

                $columns = array();
                $data = array();
                foreach ($columnarray as $val) {
                    $columns[] = $val['column'];
                    $data[] = $val['data'];
                }

                $statement .= '(' . implode(' , ', $columns) . ')';
                $statement .= ' VALUES (' . implode(' , ', $data) . ')';

                break;
        }


        $this->finishQuery = $statement;

        return array('STATEMENT' => $statement, 'BINDS' => $this->binds, 'PARAMETERS' => $this->parameters);
    }

}

/* * ****************************************************** */
/* * ****************** PROC Statement ******************** */
/* * ****************************************************** */


$procQuery = new sqlStatementBuilder('EXECUTE', 'PARAMETER');

// Set Procedure name
$procQuery->setTableOrProc('prTestProc')
        ->addColumn('@Var1', false, 'tab')
        ->addColumn('@Var2', false, 'fub');

$query0 = $procQuery->buildStatement();
var_dump($query0);


/* * ****************************************************** */
/* * ****************** Select Statement ****************** */
/* * ****************************************************** */


$selectQuery = new sqlStatementBuilder('SELECT', 'PARAMETER');
$selectQuery->setTableOrProc('firstTable') // Tabels and JOIN Data
        ->addJoin('SecondTable', 'PK1', 'Left Join', 'PK2', 'Column2')

// Column add 
// $selectQuery->addColumn('ColumnName1', 'firstTable' ,'var1'); is alsow ok
        ->addColumn('ColumnName1', 'firstTable')
        ->addColumn('ColumnName2', 'firstTable')
        ->addColumn('ColumnName3', 'SecondTable')
        ->addColumn('ColumnName4', 'SecondTable')
        ->addColumn('function(5) as Column5', false)
        ->addColumn('( 5-3 ) as Column6', false)
        ->addIdentify('PKColumn', 'firstTable', '5')
        ->addIdentify('displayoff', 'firstTable', '0');

$query1 = $selectQuery->buildStatement();
var_dump($query1);

/*
  $pdo = new PDO('TEST TNS' ,'USERNAME' ,'PASSWORT');

  $stmt = $pdo ->prepare($insertQuery->getquery());

  $stmt->execute();
 */

/* * ****************************************************** */
/* * ****************** Insert Statement ****************** */
/* * ****************************************************** */

$insertQuery = new sqlStatementBuilder('INSERT', 'DIRECT');

$insertQuery->setTableOrProc('firstTable')
        ->addColumn('ColumnName1', 'firstTable', 'Var1')
        ->addColumn('ColumnName2', 'firstTable', 'Var2')
        ->addColumn('ColumnName3', 'firstTable', 'Var3')
        ->addColumn('ColumnName4', 'firstTable', 'Var4');

$query2 = $insertQuery->buildStatement();
var_dump($query2);
/* $pdo = new PDO('TEST TNS', 'USERNAME', 'PASSWORT');

  $stmt = $pdo->prepare($insertQuery->getquery());

  $stmt->execute();
 */


/* * ****************************************************** */
/* * ****************** Update Statement ****************** */
/* * ****************************************************** */

$updateQuery = new sqlStatementBuilder('UPDATE', 'bind');

$updateQuery->setTableOrProc('firstTable')
        ->addColumn('ColumnName1', 'firstTable', 'Var1')
        ->addColumn('ColumnName2', 'firstTable', 'Var2')
        ->addColumn('ColumnName3', 'firstTable', 'Var3')
        ->addColumn('ColumnName4', 'firstTable', 'Var4')
        ->addIdentify('PKColumn', 'firstTable', '5')
        ->addIdentify('displayoff', 'firstTable', '0');

$query3 = $updateQuery->buildStatement();
var_dump($query3);

/*
$pdo = new PDO('TEST TNS', 'USERNAME', 'PASSWORT');
$stmt = $pdo->prepare($updateQuery->getquery());
foreach ($updateQuery->getbinds() as $bind => $val) {
    $stmt->bindParam($bind, $val);
}
$stmt->execute();
*/

