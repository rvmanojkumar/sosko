<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\AttributeGroupResource as AttributeGroupResource;
class AttributeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'display_type' => $this->display_type,
            'display_type_label' => $this->getDisplayTypeLabel(),
            'is_required' => (bool) $this->is_required,
            'is_filterable' => (bool) $this->is_filterable,
            'is_global' => (bool) $this->is_global,
            'sort_order' => (int) $this->sort_order,
            'description' => $this->description,
            'validation_rules' => $this->validation_rules,
            'values' => AttributeValueResource::collection($this->whenLoaded('values')),
            'groups' => AttributeGroupResource::collection($this->whenLoaded('attributeGroups')),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }

    protected function getTypeLabel()
    {
        $types = [
            'select' => 'Select Dropdown',
            'color' => 'Color Picker',
            'size' => 'Size Selector',
            'radio' => 'Radio Buttons',
            'checkbox' => 'Checkboxes',
        ];
        
        return $types[$this->type] ?? ucfirst($this->type);
    }

    protected function getDisplayTypeLabel()
    {
        $types = [
            'dropdown' => 'Dropdown',
            'button' => 'Buttons',
            'swatch' => 'Color Swatches',
        ];
        
        return $types[$this->display_type] ?? ucfirst($this->display_type);
    }
}