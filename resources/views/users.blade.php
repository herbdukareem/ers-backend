<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$permissions = json_encode(Permission::all());
$roles = json_encode(Role::all());
$users = json_encode(User::paginate(100));

?>

@extends('layouts.app')
@section('content')
<div id="appRoot2" class="w-full">

<div class="px-4 py-4 h-92.1vh w-full">
    <!-- Header Buttons -->
    <div class="alert-white px-4 py-4">   
        <button @click="tab = 4" :disabled="tab == 4" class="mx-2 bg-white text-primary-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg px-4 py-2 border border-gray-300">
            Assign Role
        </button>
        <button @click="tab = 5" :disabled="tab == 5" class="mx-2 bg-white text-primary-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg px-4 py-2 border border-gray-300">
            Assign Permission
        </button>

    </div>
    <!-- Main Content -->
    <div class="grid grid-cols-3 w-full mb-3 mx-auto py-3">
        <!-- Sidebar -->
        <div class="mx-auto w-full  col-span-1">
            <div class="bg-white py-3 rounded-lg shadow-lg h-full">
                <!-- Search Input -->
                <div class="flex items-center px-3 py-2">
                    <input type="text" v-model="search_keyword" @keyup="fetchUsers" class="flex-1 p-2 border border-r-0 border-gray-300 rounded-l-md focus:border-primary-500 focus:ring-primary-500 focus:outline-none" autocomplete="off" placeholder="Search" />
                    <button @click="fetchUsersClick" class="bg-sky-500 text-white p-2 rounded-r-md hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                        Search
                    </button>
                </div>

              <!-- User List -->
            <div class="my-2">
                <ul class="my-3 px-0 space-y-2 overflow-y-auto h-[50vh]">
                    <li v-for="(user, i) in users.data" :key="user.id" @click="selectUser(user, $event)" class="cursor-pointer">
                        <div class="bg-white shadow-md rounded-md p-3 transition duration-300 hover:bg-gray-100">
                            <div class="text-lg font-medium text-gray-800">@{{ user.first_name }} @{{ user.surname }}</div>
                            <div class="text-sm text-gray-500">@{{ user.nicare_code }}</div>
                        </div>
                    </li>
                </ul>
            </div>

                <!-- Pagination Controls -->
                <div class="flex justify-center">
                    <a :disabled="users.prev_page_url == null" :href="users.prev_page_url" class="btn btn-sky py-1 mx-1">Prev</a>
                    <a :disabled="users.next_page_url == null" :href="users.next_page_url" class="btn btn-sky py-1 mx-1">Next</a>
                </div>
            </div>
        </div>       
        <div v-if="tab == 4" class="user-list mx-auto col-span-1 px-3 w-full">
            <div style="overflow: auto;height: 72vh;" class="grid grid-cols-12 gap-4 p-3 bg-white shadow rounded">
                <div v-if="selected_user.id !== ''" class="col-span-12">
                    <template v-for="role in roles">
                        <label :key="role.id" class="block cursor-pointer flex items-center py-2 px-3 bg-gray-100 rounded-lg m-2">
                            <span v-if="role.loading" class="animate-spin h-5 w-5 mr-3 text-primary" role="status" aria-hidden="true"></span>
                            <input v-else class="mr-2" @change="assignRole(role, $event)" type="checkbox" :value="role.id" v-model="role_ids">
                            <span class="text-sm">@{{ role.name.replace('_', ' ') }}</span>
                        </label>
                    </template>
                </div>
                <div v-else class="col-span-4">
                    No User selected
                </div>
            </div>
        </div>

        <div class="user-list mx-auto col-span-1 px-3" v-if="tab == 5">
            <div style="overflow: auto;height: 72vh;" class="grid grid-cols-12 gap-4 p-3 bg-white shadow rounded">
                <div v-if="selected_user.id !== ''" class="col-span-12">
                    <template v-for="permission in permissions">
                        <label :key="permission.id" class="block cursor-pointer flex items-center py-2 px-3 bg-gray-100 rounded-lg m-2">
                            <span v-if="permission.loading" class="animate-spin h-5 w-5 mr-3 text-primary" role="status" aria-hidden="true"></span>
                            <input v-else class="mr-2" @change="assignPermission(permission, $event)" type="checkbox" :value="permission.id" v-model="permission_ids">
                            <span class="text-sm">@{{ permission.description }}</span>
                        </label>
                    </template>
                </div>
                <div v-else class="col-span-4">
                    No User selected
                </div>
            </div>
        </div>


        <transition name="fade">
        <div v-if="loading" class="bg-sky-500/70 inset-0 fixed z-[100001] flex flex-col justify-center items-center   overflow-y-auto h-full w-full" >
            <span class="loader"></span>
            <span class="text-2xl">loading</span>
        </div>
        </transition>
    </div>
</div>
</div>
<script>
    const { createApp } = Vue;

    const app = createApp({
        created() {

        },
        data() {
            return {
                permissions: <?= $permissions ?>,
                roles: <?= $roles ?>,
                tab: 4,
                permission_ids: [],
                role_ids: [],
                users: <?= $users ?>,
                selected_user: {
                    first_name: '',
                    surname: '',
                    email: '',
                    gender: '',
                    phone_number: '',
                    department_id: '',
                    unit_id: '',
                    password: '',
                    confirm_password: '',
                    id: ''
                },
                search_keyword: '',
                paginateBy: 7,
            }
        },
        methods: {
            async assignRole(role, e) {
                let type = "unassign"
                if (e.target.checked) {
                    type = "assign"
                }
                role.loading = true;
                let res = await axios.post('/assign_role', { model_id: this.selected_user.id, role: role.name, type: type });
                role.loading = false;
                if (res.status == 200) {
                    showAlert(res.data, 'success');
                } else {
                    showAlert(res.data, 'error');
                }
            },
            async assignPermission(permission, e) {
                let type = "unassign"
                if (e.target.checked) {
                    type = "assign"
                }
                permission.loading = true;
                let res = await axios.post('/assign_permission', { model_id: this.selected_user.id, permission: permission.name, type: type });
                permission.loading = false;
                if (res.status == 200) {
                    showAlert(res.data, 'success');
                } else {
                    showAlert(res.data, 'error');
                }
            },
            async fetchUsers(e) {
                if (event.key === "Enter") {
                    let res = await axios.post('/get_users', { search: this.search_keyword, paginateBy: this.paginateBy });
                    if (res.status == 200) {
                        this.users = res.data;
                    } else {
                        showAlert(res.data, 'error');
                    }
                }
            },
            async fetchUsersClick(e) {
                let res = await axios.post('/get_users', { search: this.search_keyword, paginateBy: this.paginateBy });
                if (res.status == 200) {
                    this.users = res.data;
                } else {
                    showAlert(res.data, 'error');
                }
            },
            selectUser(user, e) {
                this.permission_ids = user.permission_ids;
                this.role_ids = user.role_ids;
                this.selected_user.first_name = user.first_name;
                this.selected_user.surname = user.surname;
                this.selected_user.email = user.email;
                this.selected_user.gender = user.gender;
                this.selected_user.phone_number = user.phone_number;
                this.selected_user.department_id = user.department_id;
                this.selected_user.unit_id = user.unit_id;
                this.selected_user.id = user.id;
                document.querySelectorAll('.a-item').forEach(function(item) {
                    item.classList.remove('text-white');
                    item.classList.remove('active');
                    item.classList.add('text-dark');
                });
            },          
        },
        mounted() {
            this.$nextTick(()=>{

            
                   /*  document.getElementById('toggleCards').addEventListener('click', function() {
                        let icon = this.querySelector('i');
                        icon.classList.toggle('bi-chevron-up');
                        document.querySelector('.cards-container').classList.toggle('hidden');
                    }); */
                })
        }
    });

    app.mount('#appRoot2');
</script>

<style scoped>
/* Add your custom styles for this component */
.active a.px-2.py-1.nav-link.text-dark.a-item {
    color: white !important;
}
.active {
  background-color: var(--primary-c) !important; /* Add your preferred active background color */  
  color: #fff !important; /* Add your preferred active text color */
}
.nav-pills{
    position:static !important;
}
.nav-item{
    border-bottom: 1px solid #bbb;
}
.user-items:hover a {
    background-color: var(--primary-c) !important;
    color: white !important;
    box-shadow: 0px 1px 14px -5px #555;
}

.btn-toggle {
	display: inline-flex;
	align-items: center;
	padding: .25rem .5rem;
	font-weight: 600;
	color: rgba(0, 0, 0, .65);
	background-color: transparent;
	border: 0;
  }
  .btn-toggle:hover,
  .btn-toggle:focus {
	color: rgba(0, 0, 0, .85);
	background-color: #d2f4ea;
  }
  
  .btn-toggle::before {
	width: 1.25em;
	line-height: 0;
	content: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='rgba%280,0,0,.5%29' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 14l6-6-6-6'/%3e%3c/svg%3e");
	transition: transform .35s ease;
	transform-origin: .5em 50%;
  }
  
  .btn-toggle[aria-expanded="true"] {
	color: rgba(0, 0, 0, .85);
  }
  .btn-toggle[aria-expanded="true"]::before {
	transform: rotate(90deg);
  }
  
  .btn-toggle-nav a {
	display: inline-flex;
	padding: .1875rem .5rem;
	margin-top: .125rem;
	margin-left: 1.25rem;
	text-decoration: none;
  }
  .btn-toggle-nav a:hover,
  .btn-toggle-nav a:focus {
	background-color: #d2f4ea;
  }
  .animate-spin {     
    background-color: #555;
    border-radius: 50%;
    position: relative;
    width: 20px; /* Adjust width and height as needed */
    height: 20px; /* Adjust width and height as needed */
}

.animate-spin::after { 
    content: "";
    display: block;
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
    background-color: #aaa;
    border-radius: 50%;
}

@keyframes ping {
    75%, 100% {
        transform: scale(2);
        opacity: 0;
    }
}

</style>
@endsection

