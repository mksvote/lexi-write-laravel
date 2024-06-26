<?php

namespace Modules\CMS\Entities;

use App\Models\Model;
use App\Traits\ModelTraits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Component extends Model
{
    use HasFactory, Cachable;

    /**
     * Fillable
     *
     * @var array
     */
    protected $fillable = ['page_id', 'layout_id', 'level'];

    /**
     * Relation with ComponentProperty model
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function properties()
    {
        return $this->hasMany(\Modules\CMS\Entities\ComponentProperty::class, 'component_id', 'id');
    }

    /**
     * Relation with Page model
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function page()
    {
        return $this->belongsTo(\Modules\CMS\Entities\Page::class);
    }

    /**
     * Relation with Layout model
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function layout()
    {
        return $this->belongsTo(\Modules\CMS\Entities\Layout::class);
    }

    /**
     * Component Reorder
     *
     * @param int $page_id
     * @param int $oldLevel
     * @param int $oldLevel
     * @return void
     */
    public static function componentReorder($page_id, $oldLevel, $newLevel)
    {
        $updatableComponents = self::where('page_id', $page_id);
        if ($oldLevel) {
            if ($newLevel == $oldLevel) {
                return;
            } elseif ($newLevel > $oldLevel) {
                $updatableComponents->where('level', '>', $oldLevel)
                    ->where('level', '<=', $newLevel)
                    ->decrement('level');
            } else {
                $updatableComponents->where('level', '<', $oldLevel)
                    ->where('level', '>=', $newLevel)
                    ->increment('level');
            }
        } else {
            $updatableComponents->where('level', '>=', $newLevel)->increment('level');
        }
        return;
    }

    /**
     * Get Magic Method
     */
    public function __get($name)
    {
        $result = parent::__get($name);

        if ($result) {
            return $result;
        }

        if (!$this->propertiesLoaded()) {
            return $result;
        }

        $this->generatePropertiesArray();

        return isset($this->relations['propertiesArray'][$name]) ? $this->relations['propertiesArray'][$name] : $result;
    }

    /**
     * Isset Magic Method
     */
    public function __isset($name)
    {
        $result = parent::__get($name);

        if ($result) {
            return $result;
        }

        if (!$this->propertiesLoaded()) {
            return $result;
        }

        $this->generatePropertiesArray();

        return $this->isPropertiesArrayGenerated() && isset($this->relations['propertiesArray'][$name]);
    }

    /**
     * Properties Loaded
     *
     * @return bool
     */
    private function propertiesLoaded()
    {
        return isset($this->relations['properties']);
    }

    /**
     * Is Properties Array Generated
     *
     * @return void
     */
    private function isPropertiesArrayGenerated()
    {
        return isset($this->relations['propertiesArray']);
    }

    /**
     * Generate Properties Array
     *
     * @return void
     */
    private function generatePropertiesArray()
    {
        if ($this->propertiesLoaded() && !$this->isPropertiesArrayGenerated()) {
            $this->relations['propertiesArray'] = $this->relations['properties']->pluck('value', 'name');
        }
    }

    /**
     * Get Properties Array
     *
     * @return array
     */
    private function getPropertiesArray()
    {
        return $this->propertiesLoaded() ? $this->relations['propertiesArray'] : [];
    }
}
