// Frontend berkomunikasi dengan REST API backend. Jangan akses database langsung dari browser.
const API_URL = window.location.pathname.includes("/frontend/") ? "../backend/api.php" : "backend/api.php";

async function apiRequest(method = "GET", params = {}, body = null) {
  const url = new URL(API_URL, window.location.href);
  Object.entries(params).forEach(([key, value]) => {
    if (value !== null && value !== undefined && value !== "") url.searchParams.set(key, value);
  });
  const options = { method, headers: {} };
  if (body !== null) {
    options.headers["Content-Type"] = "application/json";
    options.body = JSON.stringify(body);
  }
  const response = await fetch(url, options);
  let result;
  try { result = await response.json(); }
  catch { throw new Error("Respons backend bukan JSON. Periksa Apache, PHP, dan endpoint API."); }
  if (!response.ok || result.status === "error") throw new Error(result.message || `HTTP ${response.status}`);
  return result;
}
function showMessage(elementId, message = "", isError = false) {
  const el = document.getElementById(elementId);
  if (!el) return;
  el.textContent = message;
  el.classList.toggle("error", isError);
}
function safeCell(value) {
  const td = document.createElement("td");
  td.textContent = value ?? "";
  return td;
}
async function loadHomeCustomers(search = "") {
  const tbody = document.getElementById("homeCustomerRows");
  if (!tbody) return;
  tbody.innerHTML = "";
  try {
    const result = await apiRequest("GET", { search });
    const customers = result.data || [];
    if (!customers.length) {
      const tr = document.createElement("tr"), td = safeCell("Data pelanggan tidak ditemukan.");
      td.colSpan = 5; tr.append(td); tbody.append(tr);
    } else customers.forEach((c, index) => {
      const tr = document.createElement("tr");
      [index + 1, c.nama, c.telepon, c.email, c.alamat].forEach(v => tr.append(safeCell(v)));
      tbody.append(tr);
    });
    showMessage("homeMessage", `${customers.length} data ditemukan.`);
  } catch (e) {
    const tr = document.createElement("tr"), td = safeCell(e.message);
    td.colSpan = 5; tr.append(td); tbody.append(tr); showMessage("homeMessage", e.message, true);
  }
}
async function loadManageCustomers(search = "") {
  const tbody = document.getElementById("manageCustomerRows");
  if (!tbody) return;
  tbody.innerHTML = "";
  try {
    const result = await apiRequest("GET", { search });
    const customers = result.data || [];
    if (!customers.length) {
      const tr = document.createElement("tr"), td = safeCell("Belum ada data pelanggan.");
      td.colSpan = 6; tr.append(td); tbody.append(tr);
    } else customers.forEach((c, index) => {
      const tr = document.createElement("tr");
      [index + 1, c.nama, c.telepon, c.email, c.alamat].forEach(v => tr.append(safeCell(v)));
      const action = document.createElement("td"); action.className = "actions-cell";
      const edit = document.createElement("button"); edit.type = "button"; edit.textContent = "Edit";
      edit.addEventListener("click", () => beginEdit(c, index + 1));
      const del = document.createElement("button"); del.type = "button"; del.textContent = "Hapus"; del.className = "secondary";
      del.addEventListener("click", () => deleteCustomer(c.id, c.nama));
      action.append(edit, del); tr.append(action); tbody.append(tr);
    });
    showMessage("manageMessage", `${customers.length} data ditemukan.`);
  } catch (e) {
    const tr = document.createElement("tr"), td = safeCell(e.message);
    td.colSpan = 6; tr.append(td); tbody.append(tr); showMessage("manageMessage", e.message, true);
  }
}
function beginEdit(customer, displayNo) {
  document.getElementById("customerId").value = customer.id;
  ["nama","telepon","email","alamat"].forEach(key => document.getElementById(key).value = customer[key] ?? "");
  document.getElementById("formTitle").textContent = `Edit Pelanggan #${displayNo ?? customer.id}`;
  document.getElementById("saveButton").textContent = "Simpan Perubahan";
  document.getElementById("cancelEdit").hidden = false;
  showMessage("formMessage", "Mode edit aktif. Ubah data lalu simpan.");
  document.getElementById("nama").focus();
  window.scrollTo({top: 0, behavior: "smooth"});
}
function resetCustomerForm() {
  document.getElementById("customerForm").reset();
  document.getElementById("customerId").value = "";
  document.getElementById("formTitle").textContent = "Tambah Pelanggan";
  document.getElementById("saveButton").textContent = "Tambahkan";
  document.getElementById("cancelEdit").hidden = true;
  showMessage("formMessage", "");
}
async function deleteCustomer(id, name) {
  if (!confirm(`Yakin ingin menghapus data pelanggan "${name}"?`)) return;
  try {
    const result = await apiRequest("DELETE", {id});
    showMessage("manageMessage", result.message || "Data berhasil dihapus.");
    await loadManageCustomers(document.getElementById("manageSearch").value.trim());
  } catch (e) { showMessage("manageMessage", e.message, true); }
}
document.addEventListener("DOMContentLoaded", () => {
  if (document.getElementById("homeCustomerRows")) {
    loadHomeCustomers();
    document.getElementById("homeSearchForm").addEventListener("submit", e => {
      e.preventDefault(); loadHomeCustomers(document.getElementById("homeSearch").value.trim());
    });
    document.getElementById("resetHomeSearch").addEventListener("click", () => {
      document.getElementById("homeSearch").value = ""; loadHomeCustomers();
    });
  }
  if (document.getElementById("customerForm")) {
    loadManageCustomers();
    document.getElementById("customerForm").addEventListener("submit", async e => {
      e.preventDefault();
      const id = document.getElementById("customerId").value;
      const payload = Object.fromEntries(["nama","telepon","email","alamat"].map(k => [k,document.getElementById(k).value.trim()]));
      try {
        const result = await apiRequest(id ? "PUT" : "POST", id ? {id} : {}, payload);
        resetCustomerForm();
        showMessage("formMessage", result.message || "Data berhasil disimpan.");
        await loadManageCustomers(document.getElementById("manageSearch").value.trim());
      } catch (err) { showMessage("formMessage", err.message, true); }
    });
    document.getElementById("cancelEdit").addEventListener("click", resetCustomerForm);
    document.getElementById("manageSearchForm").addEventListener("submit", e => {
      e.preventDefault(); loadManageCustomers(document.getElementById("manageSearch").value.trim());
    });
    document.getElementById("resetManageSearch").addEventListener("click", () => {
      document.getElementById("manageSearch").value = ""; loadManageCustomers();
    });
  }
});
