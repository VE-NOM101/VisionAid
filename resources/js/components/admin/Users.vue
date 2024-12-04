<script>
import axios from "axios";
import { onMounted, ref, computed } from "vue";
import { useToastr } from '../../toastr.js';

export default {
  setup() {
    const users = ref([]);
    const roles = ref([
      { name: "admin", value: 4 },
      { name: "doctor", value: 2 },
      { name: "user", value: 1 },
    ]);
    const current_user = ref([]);
    const searchQuery = ref(""); // Search term

    const toastr = useToastr();

    // Pagination state
    const currentPage = ref(1);
    const itemsPerPage = ref(3);

    // Computed properties
    const filteredUsers = computed(() => {
      return users.value.filter((user) =>
        `${user.name} ${user.email} ${user.address}`
          .toLowerCase()
          .includes(searchQuery.value.toLowerCase())
      );
    });

    const paginatedUsers = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value;
      const end = start + itemsPerPage.value;
      return filteredUsers.value.slice(start, end);
    });

    const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage.value));

    const getUsers = () => {
      axios
        .get('/api/_admin/getUsers')
        .then((response) => {
          users.value = response.data;
        })
        .catch((error) => {
          console.error("Failed to fetch users:", error);
        });
    };

    const changeRole = (user, role) => {
      axios
        .patch(`/api/_admin/${user.id}/changeRole`, {
          role: parseInt(role, 10), // Ensure role is a number
        })
        .then(() => {
          toastr.success("Role changed successfully");
        })
        .catch((error) => {
          console.error(`Failed to change role for user ${user.name}:`, error.response?.data || error.message);
        });
    };

    const getCurrentUser = () => {
      axios.get("/api/getMyid")
        .then((response) => {
          current_user.value = response.data;
        })
        .catch((error) => {
          console.error("Failed to fetch current user:", error);
        });
    };

    const changePage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
      }
    };

    onMounted(() => {
      getUsers();
      getCurrentUser();
    });

    return {
      users,
      roles,
      changeRole,
      current_user,
      currentPage,
      itemsPerPage,
      searchQuery,
      paginatedUsers,
      totalPages,
      changePage,
    };
  },
};
</script>
<template>
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Assign and Edit Role</h3>
            </div>
            <div class="card-body">
              <!-- Search Input -->
              <div class="row mb-3">
                <div class="col-md-6 offset-md-3">
                  <div class="input-group">
                    <input
                      type="search"
                      v-model="searchQuery"
                      class="form-control"
                      placeholder="Type here to filter users"
                    />
                    <div class="input-group-append">
                      <span class="input-group-text">
                        <i class="fa fa-search"></i>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              <!-- User Table -->
              <table class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th class="text-center">Name</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">Address</th>
                    <th class="text-center">Role</th>
                  </tr>
                </thead>
                <tbody v-if="paginatedUsers.length > 0">
                  <tr v-for="user in paginatedUsers" :key="user.id">
                    <td class="text-center">{{ user.name }}</td>
                    <td class="text-center">{{ user.email }}</td>
                    <td class="text-center">{{ user.address }}</td>
                    <td class="text-center">
                      <select class="form-control" v-model="user.role" @change="changeRole(user, user.role)"
                        :disabled="user.id === current_user.id">
                        <option v-for="role in roles" :value="role.value">{{ role.name }}</option>
                      </select>
                    </td>
                  </tr>
                </tbody>
                <tbody v-else>
                  <tr class="text-center">
                    <td colspan="4">No user data found</td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr>
                    <th class="text-center">Name</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">Address</th>
                    <th class="text-center">Role</th>
                  </tr>
                </tfoot>
              </table>
              <div class="pagination mt-3 text-center">
                <button class="btn btn-dark mx-2" @click="changePage(currentPage - 1)" :disabled="currentPage === 1">
                  <i class="fas fa-arrow-left"></i>
                </button>
                <span>Page {{ currentPage }} of {{ totalPages }}</span>
                <button class="btn btn-dark mx-2" @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages">
                  <i class="fas fa-arrow-right"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

