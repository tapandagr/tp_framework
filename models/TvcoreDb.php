<?php
/**
 * Cornelius - Core PrestaShop module
 *
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2024 © tivuno.com
 * @license   https://tivuno.com/blog/bp/business-news/2-basic-license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class TvcoreDb
{
    public static function execSql(string $file)
    {
        if (is_file($file)) {
            $sql = [];
            require_once $file;
            foreach ($sql as $s) {
                if (!Db::getInstance()->execute($s)) {
                    return;
                }
            }
        }
    }

    /**
     * It updates records in bulk on a table
     *
     * @param string $table
     * @param array $row_arrays
     *
     * @return bool
     */
    public static function update(string $table, array $row_arrays)
    {
        $table = _DB_PREFIX_ . pSQL($table);
        $update = '';
        $query = '';
        $query_columns = '';

        $query .= 'INSERT INTO ' . $table . ' (';

        $values = '';
        foreach ($row_arrays as $count => $row_array) {
            if ($count == 0) {
                $values .= '(';
            } else {
                $values .= ',(';
                $c = 0;
            }

            foreach ($row_array as $key => $value) {
                if ($count == 0) {
                    if ($query_columns) {
                        $query_columns .= ',' . pSQL($key);
                        $update .= ", $key=VALUES($key)";
                        if (is_null($value)) {
                            $values .= ',NULL';
                        } else {
                            $values .= ',"' . pSQL($value) . '"';
                        }
                    } else {
                        $query_columns .= pSQL($key);
                        $update .= "$key=VALUES($key)";
                        if (is_null($value)) {
                            $values .= 'NULL';
                        } else {
                            $values .= '"' . pSQL($value) . '"';
                        }
                    }
                } else {
                    if ($c == 0) {
                        if (is_null($value)) {
                            $values .= 'NULL';
                        } else {
                            $values .= '"' . pSQL($value) . '"';
                        }
                    } else {
                        if (is_null($value)) {
                            $values .= ',NULL';
                        } else {
                            $values .= ',"' . pSQL($value) . '"';
                        }
                    }
                    ++$c;
                }
            }

            $values .= ')';
        }

        $query .= $query_columns . ') VALUES ' . $values;
        $query .= ' ON DUPLICATE KEY UPDATE ';
        $query .= $update;

        return Db::getInstance()->execute($query);
    }

    /**
     * It gets the last position used in a table, given some condition
     *
     * @param string $table
     * @param bool|string $where
     *
     * @return float|int|string
     */
    public static function _getHigherPosition(string $table, bool|string $where = true)
    {
        $sql = new DbQuery();
        $sql->select('MAX(position)')
            ->from(pSQL($table))
            ->where(pSQL($where));
        $position = Db::getInstance()->getValue($sql);

        return (is_numeric($position)) ? $position : -1;
    }

    public static function getHigherPosition(
        string $table = 'category',
        string $where = '1',
        string $field = 'position',
    ) {
        $q = new DbQuery();
        $q->select('MAX(`' . pSQL($field) . '`)')
            ->from(pSQL($table))
            ->where(pSQL($where));

        $position = Db::getInstance()->getValue($q);

        return (is_numeric($position)) ? $position + 1 : 0;
    }

    /**
     * It moves the orphan children from the deleted parent to the dummy one
     *
     * @param string $table
     * @param string $parent_column
     * @param int|null $id_parent
     *
     * @return true
     *
     * @throws PrestaShopDatabaseException
     */
    public static function moveOrphanChildren(string $table, string $parent_column, ?int $id_parent)
    {
        $sql = new DbQuery();
        $sql->select('*')->from($table)->where($parent_column . ' = ' . (int) $id_parent);
        $orphans = Db::getInstance()->executeS($sql);
        if ($orphans) {
            $result = [];
            $key = 'id_' . $table;
            foreach ($orphans as $orphan) {
                $result[] = [
                    $key => (int) $orphan[$key],
                    $parent_column => (int) 1,
                ];
            }

            TvcoreDb::update($table, $result);
        }

        TvcoreDb::fixPositions($table, $parent_column . ' = 1', true);

        return true;
    }

    /**
     * It fixes positions when we delete or move a sibling
     *
     * @param string $table
     * @param string|int $where
     *
     * @return true
     *
     * @throws PrestaShopDatabaseException
     */
    public static function fixPositions(string $table, string|int $where = 1)
    {
        $sql = new DbQuery();
        $sql->select('*')->from($table)->where(pSQL($where))->orderBy('position asc');
        $data = Db::getInstance()->executeS($sql);

        if ($data) {
            $position = 0;
            $positions = [];
            $key = 'id_' . $table;
            foreach ($data as $datum) {
                $positions[] = [
                    $key => (int) $datum[$key],
                    'position' => (int) $position,
                ];

                ++$position;
            }

            TvcoreDb::update($table, $positions);
        }

        return true;
    }

    /**
     * It returns a record id based on a condition.
     * In case it does not exist, it returns false.
     *
     * @param string $id
     * @param string $table
     * @param string $where
     * @return false|string
     */
    public static function getRecordId(string $id, string $table, string $where)
    {
        $sql = new DbQuery();
        $sql->select(pSQL($id))->from(pSQL($table))->where($where);

        return Db::getInstance()->getValue($sql);
    }
}
