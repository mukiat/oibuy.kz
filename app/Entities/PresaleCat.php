<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PresaleCat
 */
class PresaleCat extends Model
{
    protected $table = 'presale_cat';

    protected $primaryKey = 'cat_id';

    public $timestamps = false;

    protected $fillable = [
        'cat_name',
        'keywords',
        'cat_desc',
        'measure_unit',
        'show_in_nav',
        'style',
        'is_show',
        'grade',
        'filter_attr',
        'is_top_style',
        'top_style_tpl',
        'cat_icon',
        'is_top_show',
        'category_links',
        'category_topic',
        'pinyin_keyword',
        'cat_alias_name',
        'template_file',
        'parent_id',
        'sort_order'
    ];

    protected $guarded = [];


    /**
     * @return mixed
     */
    public function getCatName()
    {
        return $this->cat_name;
    }

    /**
     * @return mixed
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @return mixed
     */
    public function getCatDesc()
    {
        return $this->cat_desc;
    }

    /**
     * @return mixed
     */
    public function getMeasureUnit()
    {
        return $this->measure_unit;
    }

    /**
     * @return mixed
     */
    public function getShowInNav()
    {
        return $this->show_in_nav;
    }

    /**
     * @return mixed
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * @return mixed
     */
    public function getIsShow()
    {
        return $this->is_show;
    }

    /**
     * @return mixed
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * @return mixed
     */
    public function getFilterAttr()
    {
        return $this->filter_attr;
    }

    /**
     * @return mixed
     */
    public function getIsTopStyle()
    {
        return $this->is_top_style;
    }

    /**
     * @return mixed
     */
    public function getTopStyleTpl()
    {
        return $this->top_style_tpl;
    }

    /**
     * @return mixed
     */
    public function getCatIcon()
    {
        return $this->cat_icon;
    }

    /**
     * @return mixed
     */
    public function getIsTopShow()
    {
        return $this->is_top_show;
    }

    /**
     * @return mixed
     */
    public function getCategoryLinks()
    {
        return $this->category_links;
    }

    /**
     * @return mixed
     */
    public function getCategoryTopic()
    {
        return $this->category_topic;
    }

    /**
     * @return mixed
     */
    public function getPinyinKeyword()
    {
        return $this->pinyin_keyword;
    }

    /**
     * @return mixed
     */
    public function getCatAliasName()
    {
        return $this->cat_alias_name;
    }

    /**
     * @return mixed
     */
    public function getTemplateFile()
    {
        return $this->template_file;
    }

    /**
     * @return mixed
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * @return mixed
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setCatName($value)
    {
        $this->cat_name = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setKeywords($value)
    {
        $this->keywords = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setCatDesc($value)
    {
        $this->cat_desc = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setMeasureUnit($value)
    {
        $this->measure_unit = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setShowInNav($value)
    {
        $this->show_in_nav = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setStyle($value)
    {
        $this->style = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setIsShow($value)
    {
        $this->is_show = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setGrade($value)
    {
        $this->grade = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setFilterAttr($value)
    {
        $this->filter_attr = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setIsTopStyle($value)
    {
        $this->is_top_style = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setTopStyleTpl($value)
    {
        $this->top_style_tpl = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setCatIcon($value)
    {
        $this->cat_icon = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setIsTopShow($value)
    {
        $this->is_top_show = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setCategoryLinks($value)
    {
        $this->category_links = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setCategoryTopic($value)
    {
        $this->category_topic = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setPinyinKeyword($value)
    {
        $this->pinyin_keyword = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setCatAliasName($value)
    {
        $this->cat_alias_name = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setTemplateFile($value)
    {
        $this->template_file = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setParentId($value)
    {
        $this->parent_id = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setSortOrder($value)
    {
        $this->sort_order = $value;
        return $this;
    }
}
