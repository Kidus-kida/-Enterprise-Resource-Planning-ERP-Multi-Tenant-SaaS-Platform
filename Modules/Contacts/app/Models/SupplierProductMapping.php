<?php

namespace Modules\Contacts\Models;

use App\Models\TenantModel;

use Modules\Contacts\Models\Contact;
use App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierProductMapping extends TenantModel
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function supplier()
    {
        return $this->belongsTo(Contact::class, 'supplier_id');
    }

    // Assuming we have a Product model. If not, this relationship will be invalid until we create it.
    // Given the task is about contacts, we might not have full product logic yet, but we will add the relation.
    public function product()
    {
         return $this->belongsTo(Product::class, 'product_id'); 
    }
}

