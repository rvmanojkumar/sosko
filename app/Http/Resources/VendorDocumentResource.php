<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VendorDocumentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'vendor_profile_id' => $this->vendor_profile_id,
            'document_type' => $this->document_type,
            'document_type_label' => $this->getDocumentTypeLabel(),
            'document_number' => $this->document_number,
            'document_url' => $this->document_path ? Storage::disk('public')->url($this->document_path) : null,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'remarks' => $this->remarks,
            'verified_at' => $this->verified_at ? $this->verified_at->toISOString() : null,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }

    protected function getDocumentTypeLabel()
    {
        $types = [
            'pan' => 'PAN Card',
            'gst' => 'GST Certificate',
            'bank_statement' => 'Bank Statement',
            'address_proof' => 'Address Proof',
        ];
        
        return $types[$this->document_type] ?? ucfirst($this->document_type);
    }

    protected function getStatusLabel()
    {
        $statuses = [
            'pending' => 'Pending Verification',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
        ];
        
        return $statuses[$this->status] ?? ucfirst($this->status);
    }
}