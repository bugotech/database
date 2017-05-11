<?php namespace Bugotech\Database\Schema;

use Illuminate\Database\Schema\Builder;

class Table extends \Illuminate\Database\Schema\Blueprint
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @param Builder $builder
     * @param
     * @param $table
     * @param callable $callback
     */
    public function __construct(Builder $builder, $table, \Closure $callback = null)
    {
        $this->builder = $builder;
        $this->table = $table;
        parent::__construct($table, $callback);
    }

    /**
     * Criar campo primario padrao.
     */
    public function key()
    {
        $col = $this->bigIncrements(self::keyAttr());

        return $col;
    }

    /**
     * Criar campo primario padrao.
     */
    public function uniqueKey($column, $length = 255)
    {
        $col = $this->string($column, $length);
        $this->primary($column);

        return $col;
    }

    /**
     * Criar campo do iqnuilino.
     */
    public function tenant()
    {
        return $this->association(self::tenantField(), self::tenantTable());
    }

    /**
     * Campo de extensao.
     */
    /*
    public function extend($table)
    {
        // Criar campo
        $col = $this->bigInteger(self::keyAttr(), false, true);
        $col->nullable(true);
        $col->unsigned(true);

        // Criar constrain
        $fk = $this->foreign(self::keyAttr());
        $fk->on($table);
        $fk->references(self::keyAttr());

        // Primario
        $this->primary(self::keyAttr());

        return $col;
    }
    /**/

    /**
     * Campo String (varchar).
     */
    public function string($column, $length = 255)
    {
        $col = parent::string(strtolower($column), $length);
        $col->nullable(true);

        return $col;
    }

    /**
     * Campo Inteiro.
     */
    public function integer($column, $unsigned = false, $autoIncrement = false)
    {
        $col = parent::integer(strtolower($column), $autoIncrement, $unsigned);
        $col->default(0);

        return $col;
    }

    /**
     * Campo Numero.
     */
    public function number($column, $total = 20, $places = 5)
    {
        $col = $this->decimal(strtolower($column), $total, $places);
        $col->default(0);

        return $col;
    }

    /**
     * Campo Logico.
     */
    public function boolean($column)
    {
        $col = $this->tinyInteger(strtolower($column), false, true);
        $col->default(0);

        return $col;
    }

    /**
     * Campo DATA e HORA.
     */
    public function dateTime($column)
    {
        $col = parent::dateTime(strtolower($column));
        $col->nullable(true);

        return $col;
    }

    /**
     * Campo DATA.
     */
    public function date($column)
    {
        $col = parent::date(strtolower($column));
        $col->nullable(true);

        return $col;
    }

    /**
     * Campo HORA.
     */
    public function time($column)
    {
        $col = parent::time(strtolower($column));
        $col->nullable(true);

        return $col;
    }

    /**
     * Campo TEXTO.
     */
    public function text($column)
    {
        $col = parent::text(strtolower($column));
        $col->nullable(true);

        return $col;
    }

    /**
     * Campo de associacao (Lookup).
     */
    public function association($column, $fktable)
    {
        // Criar campo
        $col = $this->bigInteger($column, false, true);
        $col->nullable(true);
        $col->unsigned(true);

        // Criar constrain
        $this->foreignAssociation($column, $fktable);

        return $col;
    }

    /**
     * Campo LISTA.
     */
    public function options($column)
    {
        $col = parent::string($column, 5);
        $col->nullable(true);

        return $col;
    }

    /**
     * Excluir campo.
     *
     * @param  string|array $columns
     *
     * @return \Illuminate\Support\Fluent
     */
    public function dropColumn($columns)
    {
        $columns = is_array($columns) ? $columns : (array) func_get_args();

        // Verificar se deve excluir constrain dos campos lookups antes
        foreach ($columns as $column) {
            if (ForeignKey::isAssociation($column)) {
                parent::dropForeign(ForeignKey::makeName($this->table, $column));
            }
        }

        return parent::dropColumn($columns);
    }

    /**
     * Criar indice, respeitando se deve ou nao adicionar o inquilno.
     */
    public function index($columns, $name = null, $withTenant = false)
    {
        $columns = (array) $columns;

        // Verificar se deve adicionar a coluna do inquilino
        if ($withTenant && (in_array(self::tenantField(), $columns) != true)) {
            $columns = array_merge([], [self::tenantField()], $columns);
        }

        return parent::index($columns, $name);
    }

    /**
     * Criar indice unico, respeitando se deve ou nao adicionar o inquilno.
     */
    public function unique($columns, $name = null, $withTenant = false)
    {
        $columns = (array) $columns;

        // Verificar se deve adicionar a coluna do inquilino
        if ($withTenant && (in_array(self::tenantField(), $columns) != true)) {
            $columns = array_merge([], [self::tenantField()], $columns);
        }

        return parent::unique($columns, $name);
    }

    /**
     * Criar a ForeignKey da Associação.
     * @param $column
     * @param $other_table
     * @param null $name
     * @param null $other_key
     * @return \Illuminate\Support\Fluent
     */
    public function foreignAssociation($column, $other_table, $name = null, $other_key = null)
    {
        $name = is_null($name) ? ForeignKey::makeName($this->table, $column) : $name;
        $other_key = is_null($other_key) ? self::keyAttr() : $other_key;

        $fk = $this->foreign($column, $name);
        $fk->on($other_table);
        $fk->references($other_key);

        return $fk;
    }

    /**
     * Nome do atributo KEY.
     *
     * @return string
     */
    public static function keyAttr()
    {
        return app('config')->get('database.key_attr', 'id');
    }

    /**
     * Nome da tabela de inquilinos.
     *
     * @return string
     */
    public static function tenantTable()
    {
        return app('config')->get('database.tenant.table', 'inquilinos');
    }

    /**
     * Nome do model de inquilinos.
     *
     * @return string
     */
    public static function tenantModel()
    {
        return app('config')->get('database.tenant.model', null);
    }

    /**
     * Nome do atributo inquilino.
     *
     * @return string
     */
    public static function tenantAttr()
    {
        return app('config')->get('database.tenant.attribute', 'inquilino');
    }

    /**
     * Nome do campo inquilino na tabela.
     *
     * @return string
     */
    public static function tenantField()
    {
        return sprintf('%s_%s', self::tenantAttr(), self::keyAttr());
    }
}