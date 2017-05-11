<?php namespace Bugotech\Database\Migration;

use Bugotech\Database\Schema\Table;

class Migration extends \Illuminate\Database\Migrations\Migration
{
    /**
     * @var \Illuminate\Database\Connection
     */
    protected $con;

    /**
     * @var \Illuminate\Database\Schema\Builder
     */
    protected $builder;

    /**
     * @var \Illuminate\Database\Schema\Grammars\Grammar
     */
    protected $grammar;

    /**
     * Preparar Migration.
     */
    public function __construct()
    {
        $this->con = app('db')->connection($this->getConnection());
        $this->builder = $this->con->getSchemaBuilder();
        $this->grammar = $this->con->getSchemaGrammar();
    }

    public function create($table, \Closure $callback)
    {
        $tb = new Table($this->builder, $table);

        $tb->create();
        $tb->engine = 'InnoDB';

        $callback($tb);

        $tb->build($this->con, $this->grammar);
    }

    public function table($table, \Closure $callback)
    {
        $tb = new Table($this->builder, $table);

        $callback($tb);

        $tb->build($this->con, $this->grammar);
    }

    public function drop($table)
    {
        $this->builder->dropIfExists($table);
    }

    /**
     * Executar seed.
     *
     * @param $class
     */
    protected function seed($class)
    {
        \Artisan::call('db:seed', ['--class' => $class]);
    }

    /**
     * Executar migracao para atualizar versao.
     */
    public function up()
    {
        //...
    }

    /**
     * Executar migracao para desinstalar versao.
     */
    public function down()
    {
        //...
    }
}