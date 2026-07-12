<x-app-layout>
<div class="container">

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header text-center">{{ __('Profile Dashboard') }}</div>

                <div class="card-body">

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif


                    <section class="section profile">
                        <div class="row">
                          <div class="col-xl-5">

                            <div class="card">
                              <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">

                                <img src="{{ $imageUrl }}" style="max-width: 10rem; min-width: 10rem;" alt="Profile Image" class="profileImagePreview rounded-circle">

                                <h3>{{ $user->name }}</h3>
                                @if($user->roles->isNotEmpty())
                                    @foreach($roles as $role)
                                        @if($user->hasRole($role->name) == true)
                                            <h4>Act as : {{ ucfirst($role->name) }}</h4>
                                        @endif
                                    @endforeach
                                @else
                                    <p class="p-0 m-0 pt-2">Hi, Your Registration is Completed.</p>
                                    <p class="p-1 m-0 fw-semibold">But No Role Assigned.</p>
                                    <p class="p-0 m-0">Wait for Admin Approval.</p>
                                    <p class="p-0 m-0">OR Contact Super Admin for More Information</p>
                                @endif

                                <div class="social-links mt-2">
                                  <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>
                                  <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
                                  <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
                                  <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
                                </div>
                              </div>
                            </div>

                            <div class="row pt-3">
                                @canany(['create-role', 'edit-role', 'delete-role'])
                                    <a class="btn btn-primary col-md mx-1" href="{{ route('roles.index') }}">
                                        <i class="fa-solid fa-users-gear"></i> Manage Roles</a>
                                @endcanany
                                @canany(['create-user', 'edit-user', 'delete-user'])
                                    <a class="btn btn-success col-md mx-1" href="{{ route('users.index') }}">
                                        <i class="fa-solid fa-user-gear"></i> Manage Users</a>
                                @endcanany
                                @canany(['create', 'edit', 'delete','view'])
                                    <a class="btn btn-info col-md mx-1" href="{{ route('dashboard') }}"><i class="fa-brands fa-squarespace"></i> {{ __('Dashboard') }}</a>
                                @endcanany
                                <p>&nbsp;</p>
                            </div>

                          </div>

                          <div class="col-xl-7">

                            <div class="card">
                              <div class="card-body pt-3">
                                <!-- Bordered Tabs -->
                                <ul class="nav nav-tabs nav-tabs-bordered" role="tablist">

                                  <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview" aria-selected="true" role="tab">Overview</button>
                                  </li>

                                  <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit" aria-selected="false" tabindex="-1" role="tab">Edit Profile</button>
                                  </li>
                                  <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password" aria-selected="false" tabindex="-1" role="tab">Change Password</button>
                                  </li>

                                </ul>
                                <div class="tab-content pt-2">

                                <div class="tab-pane fade show active profile-overview" id="profile-overview" role="tabpanel">
                                    <h5 class="card-title">Profile Details</h5>
                                    <div class="row">
                                        @foreach($roles as $role)
                                            @if($user->hasRole($role->name) == true)
                                                <li class="label fw-bold">{{ ucfirst($role->name) }} Role: Yes</li>
                                            @endif
                                        @endforeach
                                        @foreach($permissions as $permission)
                                            @if($user->can($permission->name))
                                                <li >{{ ucfirst($permission->name) }} permission: Yes</li>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                <div class="tab-pane fade profile-edit pt-3" id="profile-edit" role="tabpanel">
                                    <!-- Profile Edit Form -->
                                    <form action="" method="post">
                                        <div class="row mb-3">
                                            <label for="" class="col-md-4 col-lg-3 col-form-label">Profile Image</label>
                                            <div class="col-md-8 col-lg-9">
                                                <img  class="profileImagePreview img-thumbnail" src="{{ $imageUrl }}" style="max-width: 5rem; min-width: 4rem;" alt="Profile">
                                                <div class="pt-2">
                                                    <label for="image" class="btn btn-primary btn-sm">
                                                        <i class="fa-solid fa-upload"></i>
                                                    </label>
                                                    <input type="file" class="btn btn-info form-control d-none" id="image" name="image" accept="image/*">
                                                    <a href="#" class="btn btn-danger btn-sm" title="Remove my profile image"><i class="fa-solid fa-trash"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>

                                    <form action="{{ route('user.profile.update') }}" method="get" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row mb-3">
                                            <label for="name" class="col-md-4 col-lg-3 col-form-label">Full Name</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="name" type="text" class="form-control" id="name" value="{{ $user->name }}">
                                                <span class="text-danger">{{ $errors->first('name') }}</span>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="rank" class="col-md-4 col-lg-3 col-form-label">Rank</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="rank" type="text" class="form-control" id="rank" value="{{ $user->rank }}">
                                                <span class="text-danger">{{ $errors->first('rank') }}</span>
                                            </div>
                                        </div>

                                      {{-- <div class="row mb-3">
                                        <label for="Address" class="col-md-4 col-lg-3 col-form-label">Address</label>
                                        <div class="col-md-8 col-lg-9">
                                          <input name="address" type="text" class="form-control" id="Address" value="A108 Adam Street, New York, NY 535022">
                                        </div>
                                      </div> --}}

                                      <div class="row mb-3">
                                        <label for="mobile" class="col-md-4 col-lg-3 col-form-label">Mobile No</label>
                                        <div class="col-md-8 col-lg-9">
                                            <div wire:ignore>
                                                <input name="mobile" type="text" class="form-control" id="mobile" x-data="intlTelInput()" value="{{ $user->mobile }}">
                                            </div>
                                            <span class="text-danger">{{ $errors->first('mobile') }}</span>
                                        </div>
                                      </div>

                                        <div class="row mb-3">
                                            <label for="Email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="email" type="email" class="form-control" id="Email" value="{{ $user->email }}">
                                                <span class="text-danger">{{ $errors->first('email') }}</span>
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form><!-- End Profile Edit Form -->
                                </div>

                                <div class="tab-pane fade pt-3" id="profile-change-password" role="tabpanel">
                                    <!-- Change Password Form -->
                                    <form action="{{ route('user.password.update') }}" method="get" enctype="multipart/form-data">
                                        @csrf
                                        @method('GET')
                                      <div class="row mb-3">
                                        <label for="currentPassword" class="col-md-4 col-lg-3 col-form-label">Current Password</label>
                                        <div class="col-md-8 col-lg-9">
                                          <input name="currentPassword" type="password" class="form-control" id="currentPassword">
                                        </div>
                                      </div>

                                      <div class="row mb-3">
                                        <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">New Password</label>
                                        <div class="col-md-8 col-lg-9">
                                          <input name="newpassword" type="password" class="form-control" id="newPassword">
                                        </div>
                                      </div>

                                      <div class="row mb-3">
                                        <label for="reEnterNewPassword" class="col-md-4 col-lg-3 col-form-label">Re-enter New Password</label>
                                        <div class="col-md-8 col-lg-9">
                                          <input name="reEnterNewPassword" type="password" class="form-control" id="reEnterNewPassword">
                                        </div>
                                      </div>

                                      <div class="text-center">
                                        <button type="submit" class="btn btn-primary">Change Password</button>
                                      </div>
                                    </form><!-- End Change Password Form -->

                                  </div>

                                </div><!-- End Bordered Tabs -->

                              </div>
                            </div>

                          </div>
                        </div>
                      </section>

                    <br/>

                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')

<script type="text/javascript">

$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#image').on('change', function() {
        var fileInput = $(this)[0];
        if (fileInput.files && fileInput.files[0]) {
            var formData = new FormData();
            formData.append('image', fileInput.files[0]);

            $.ajax({
                url: '{{ route("user.profile.upload") }}',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                },
                error: function(xhr) {
                    alert('An error occurred: ' + xhr.statusText);
                }
            });
        }

        let reader = new FileReader();
        reader.onload = function(e) {
            $('.profileImagePreview').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(this.files[0]);
    });
});

</script>

@endpush
</x-app-layout>
