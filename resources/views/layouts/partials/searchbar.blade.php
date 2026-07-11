{{-- search --}}
<ul class="navbar-nav align-items-center d-none d-lg-block">
    <li class="nav-item">
        <div class="search-box" data-list='{"valueNames":["title"]}'>
            <form class="position-relative" data-bs-toggle="search" data-bs-display="static">
                <input class="form-control search-input fuzzy-search" type="search" placeholder="{{ __('Search...') }}" aria-label="Search" />
                <i class="fas fa-search search-box-icon"></i>
            </form>
            <div class="btn-close-container position-absolute end-0 top-50 translate-middle shadow-none" data-bs-dismiss="search">
                <button class="btn btn-link btn-close p-0" aria-label="Close"></button>
            </div>
            <div class="dropdown-menu border font-base start-0 mt-2 py-0 overflow-hidden w-100">
                <div class="scrollbar list py-3" style="max-height: 24rem;">
                    <h6 class="dropdown-header fw-medium text-uppercase px-x1 fs-11 pt-0 pb-2">{{ __('Recently Browsed') }}</h6>
                    <a class="dropdown-item fs-10 px-x1 py-1 hover-primary" href="app/events/event-detail.html">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-circle me-2 text-300 fs-11"></span>
                            <div class="fw-normal title">Pages <span class="fas fa-chevron-right mx-1 text-500 fs-11"></span> Events</div>
                        </div>
                    </a>
                    <hr class="text-200 dark__text-900" />
                    <h6 class="dropdown-header fw-medium text-uppercase px-x1 fs-11 pt-0 pb-2">{{ __('Suggested Filter') }}</h6>
                    <a class="dropdown-item px-x1 py-1 fs-9" href="{{route('customers.index')}}">
                        <div class="d-flex align-items-center"><span class="badge fw-medium text-decoration-none me-2 badge-subtle-warning">customers:</span>
                            <div class="flex-1 fs-10 title">{{ __('All customers list') }}</div>
                        </div>
                    </a>
                    <a class="dropdown-item px-x1 py-1 fs-9" href="app/events/event-detail.html">
                        <div class="d-flex align-items-center"><span class="badge fw-medium text-decoration-none me-2 badge-subtle-success">events:</span>
                            <div class="flex-1 fs-10 title">{{ __('Latest events in current month') }}</div>
                        </div>
                    </a>
                    <a class="dropdown-item px-x1 py-1 fs-9" href="app/e-commerce/product/product-grid.html">
                        <div class="d-flex align-items-center"><span class="badge fw-medium text-decoration-none me-2 badge-subtle-info">products:</span>
                            <div class="flex-1 fs-10 title">{{ __('Most popular products') }}</div>
                        </div>
                    </a>
                    {{-- if need --}}
                    {{-- <hr class="text-200 dark__text-900" /> --}}
                    {{-- <h6 class="dropdown-header fw-medium text-uppercase px-x1 fs-11 pt-0 pb-2">Files</h6> --}}
                    {{-- <a class="dropdown-item px-x1 py-2" href="#!">
                        <div class="d-flex align-items-center">
                            <div class="file-thumbnail me-2"><img class="border h-100 w-100 object-fit-cover rounded-3" src="assets/img/products/3-thumb.png" alt="" /></div>
                            <div class="flex-1">
                                <h6 class="mb-0 title">iPhone</h6>
                                <p class="fs-11 mb-0 d-flex"><span class="fw-semi-bold">Antony</span><span class="fw-medium text-600 ms-2">27 Sep at 10:30 AM</span></p>
                            </div>
                        </div>
                    </a> --}}
                    <hr class="text-200 dark__text-900" />
                    <h6 class="dropdown-header fw-medium text-uppercase px-x1 fs-11 pt-0 pb-2">{{ __('Members') }}</h6>
                    {{-- <a class="dropdown-item px-x1 py-2" href="pages/user/profile.html">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-l status-online me-2">
                                <img class="rounded-circle" src="assets/img/team/1.jpg" alt="" />
                            </div>
                            <div class="flex-1">
                                <h6 class="mb-0 title">Anna Karinina</h6>
                                <p class="fs-11 mb-0 d-flex">Technext Limited</p>
                            </div>
                        </div>
                    </a> --}}
                </div>
                <div class="text-center mt-n3">
                    <p class="fallback fw-bold fs-8 d-none">{{ __('No Result Found.') }}</p>
                </div>
            </div>
        </div>
    </li>
</ul>