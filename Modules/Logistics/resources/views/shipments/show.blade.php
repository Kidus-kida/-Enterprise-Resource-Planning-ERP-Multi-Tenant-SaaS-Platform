@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Shipment Details: {{ $shipment->shipment_no }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('logistics.shipments.index') }}">Shipments</a></li>
                    <li class="breadcrumb-item active">View</li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="{{ route('logistics.shipments.edit', $shipment->id) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Edit</a>
            </div>
        </div>
    </div>

    <!-- Shipment Info Cards -->
    <div class="row">
        <!-- Basic Info -->
        <div class="col-md-6 d-flex">
            <div class="card profile-box flex-fill">
                <div class="card-body">
                    <h3 class="card-title">Shipment Information <span class="badge badge-inverse-primary float-end">{{ ucfirst(str_replace('_', ' ', $shipment->status)) }}</span></h3>
                    <ul class="personal-info list-unstyled">
                        <li>
                            <div class="title">Vendor</div>
                            <div class="text">{{ $shipment->vendor }} ({{ $shipment->vendor_country }})</div>
                        </li>
                        <li>
                            <div class="title">PO Reference</div>
                            <div class="text">{{ $shipment->po_reference ?? 'N/A' }}</div>
                        </li>
                        <li>
                            <div class="title">Incoterms</div>
                            <div class="text">{{ $shipment->incoterms }}</div>
                        </li>
                         <li>
                            <div class="title">Transport Mode</div>
                            <div class="text">{{ ucfirst($shipment->transport_mode) }}</div>
                        </li>
                        <li>
                            <div class="title">Ports</div>
                            <div class="text">{{ $shipment->port_of_loading }} <i class="fa fa-arrow-right"></i> {{ $shipment->port_of_discharge }}</div>
                        </li>
                        <li>
                            <div class="title">Expected Arrival</div>
                            <div class="text">{{ $shipment->expected_arrival ? $shipment->expected_arrival->format('d M Y') : '-' }}</div>
                        </li>
                         <li>
                            <div class="title">Dry Port</div>
                            <div class="text">{{ $shipment->dryPort->name ?? 'N/A' }}</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Status / Timeline Placeholder -->
         <div class="col-md-6 d-flex">
            <div class="card profile-box flex-fill">
                <div class="card-body">
                    <h3 class="card-title">Status & Customs</h3>
                    <ul class="personal-info list-unstyled">
                         <li>
                            <div class="title">Customs Declaration</div>
                            <div class="text">
                                @if($shipment->customsDeclaration)
                                    <a href="{{ route('logistics.customs.show', $shipment->customsDeclaration->id) }}">{{ $shipment->customsDeclaration->declaration_no }}</a>
                                    <span class="badge bg-{{ $shipment->customsDeclaration->risk_channel == 'green' ? 'success' : ($shipment->customsDeclaration->risk_channel == 'yellow' ? 'warning' : 'danger') }}">{{ ucfirst($shipment->customsDeclaration->risk_channel) }} Channel</span>
                                @else
                                    <span class="text-muted">Not declared yet</span>
                                    <a href="{{ route('logistics.customs.create', ['shipment_id' => $shipment->id]) }}" class="btn btn-sm btn-outline-primary float-end">Create Declaration</a>
                                @endif
                            </div>
                        </li>
                         <li>
                            <div class="title">Duty Status</div>
                            <div class="text">{{ $shipment->customsDeclaration ? ucfirst($shipment->customsDeclaration->status) : '-' }}</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card tab-box">
        <div class="row user-tabs">
            <div class="col-lg-12 col-md-12 col-sm-12 line-tabs">
                <ul class="nav nav-tabs nav-tabs-bottom">
                    <li class="nav-item"><a href="#containers" data-bs-toggle="tab" class="nav-link active">Containers <span class="badge rounded-pill bg-primary">{{ $shipment->containers->count() }}</span></a></li>
                    <li class="nav-item"><a href="#documents" data-bs-toggle="tab" class="nav-link">Documents <span class="badge rounded-pill bg-primary">{{ $shipment->documents->count() }}</span></a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="tab-content">
        <!-- Containers Tab -->
        <div class="tab-pane fade show active" id="containers">
             <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col text-end">
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#add_container_modal"><i class="fa fa-plus"></i> Add Container</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0">
                            <thead>
                                <tr>
                                    <th>Container No</th>
                                    <th>Size/Type</th>
                                    <th>Seal No</th>
                                    <th>Status</th>
                                    <th>Demurrage Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shipment->containers as $container)
                                <tr>
                                    <td>{{ $container->container_no }}</td>
                                    <td>{{ $container->size }} {{ ucfirst($container->type) }}</td>
                                    <td>{{ $container->seal_no ?? '-' }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $container->status)) }}</td>
                                    <td class="{{ $container->demurrage_days > 0 ? 'text-danger' : '' }}">{{ $container->demurrage_days }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No containers added</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
             </div>
        </div>

        <!-- Documents Tab -->
        <div id="documents" class="tab-pane fade">
            <div class="card">
                <div class="card-body">
                     <div class="row mb-3">
                        <div class="col text-end">
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#upload_document_modal"><i class="fa fa-upload"></i> Upload Document</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Uploaded At</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shipment->documents as $doc)
                                <tr>
                                    <td>{{ $doc->name }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $doc->type)) }}</td>
                                    <td>{{ $doc->uploaded_at->format('d M Y H:i') }}</td>
                                    <td><span class="badge bg-{{ $doc->status == 'approved' ? 'success' : ($doc->status == 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($doc->status) }}</span></td>
                                    <td>
                                        <a href="{{ route('logistics.documents.download', $doc->id) }}" class="btn btn-sm btn-secondary"><i class="fa fa-download"></i></a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No documents uploaded</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Container Modal -->
    <div id="add_container_modal" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Container</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('logistics.containers.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="shipment_id" value="{{ $shipment->id }}">
                        <div class="form-group">
                            <label>Container No <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="container_no" required>
                        </div>
                        <div class="form-group">
                            <label>Size <span class="text-danger">*</span></label>
                            <select class="select" name="size">
                                <option value="20ft">20ft</option>
                                <option value="40ft">40ft</option>
                                <option value="45ft">45ft</option>
                                <option value="LCL">LCL</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Type <span class="text-danger">*</span></label>
                            <select class="select" name="type">
                                <option value="dry">Dry</option>
                                <option value="reefer">Reefer</option>
                                <option value="flat_rack">Flat Rack</option>
                                <option value="open_top">Open Top</option>
                            </select>
                        </div>
                         <div class="submit-section">
                            <button class="btn btn-primary submit-btn">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Upload Document Modal -->
    <div id="upload_document_modal" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                 <div class="modal-header">
                    <h5 class="modal-title">Upload Document</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                 <div class="modal-body">
                    <form action="{{ route('logistics.documents.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="shipment_id" value="{{ $shipment->id }}">
                        <div class="form-group">
                            <label>Document Name <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Type <span class="text-danger">*</span></label>
                            <select class="select" name="type">
                                <option value="invoice">Invoice</option>
                                <option value="packing_list">Packing List</option>
                                <option value="bill_of_lading">Bill of Lading</option>
                                <option value="insurance">Insurance</option>
                                <option value="certificate_of_origin">Certificate of Origin</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>File <span class="text-danger">*</span></label>
                            <input class="form-control" type="file" name="file" required>
                        </div>
                         <div class="submit-section">
                            <button class="btn btn-primary submit-btn">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
