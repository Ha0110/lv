import { useState } from "react";
import { Link } from "react-router-dom";
import AdminCatalog from "./admin/AdminCatalog";
import AdminProducts from "./admin/AdminProducts";
import AdminUsers from "./admin/AdminUsers";
import { getCurrentUser, roleLabels } from "./admin/adminUtils";

function AdminAccess({ type }) {
  const title = type === "login" ? "Cần đăng nhập" : "Không có quyền truy cập";
  const message =
    type === "login"
      ? "Vui lòng đăng nhập bằng tài khoản admin hoặc owner."
      : "Trang này dành cho tài khoản admin và owner.";

  return (
    <div className="admin-page admin-access">
      <div className="admin-access-panel">
        <span className="admin-kicker">Admin</span>
        <h1>{title}</h1>
        <p>{message}</p>
        <Link to="/login" className="btn btn-primary">
          Đăng nhập
        </Link>
      </div>
    </div>
  );
}

// Trang Admin chỉ điều phối layout và quyền; từng chức năng nằm trong component riêng.
export default function Admin() {
  const currentUser = getCurrentUser();
  const canAccessAdmin = ["admin", "owner"].includes(currentUser?.role);
  // Các quyền nguy hiểm được truyền xuống tab con để ẩn nút xóa/đổi quyền với admin thường.
  const canEditRoles = currentUser?.role === "owner";
  const canDeleteProducts = currentUser?.role === "owner";
  const canDeleteCatalog = currentUser?.role === "owner";
  const [activeSection, setActiveSection] = useState("users");

  if (!currentUser) {
    return <AdminAccess type="login" />;
  }

  if (!canAccessAdmin) {
    return <AdminAccess type="forbidden" />;
  }

  return (
    <div className="admin-page">
      <section className="admin-hero">
        <div>
          <span className="admin-kicker">Quản trị cửa hàng</span>
          <h1>Bảng điều khiển</h1>
          <p>{currentUser.hoTen || currentUser.email}</p>
        </div>

        <div className="admin-identity">
          <span>
            {String(currentUser.hoTen || currentUser.email || "A")
              .charAt(0)
              .toUpperCase()}
          </span>
          <div>
            <strong>{roleLabels[currentUser.role] || currentUser.role}</strong>
            <small>{currentUser.email}</small>
          </div>
        </div>
      </section>

      <div className="admin-layout">
        <aside className="admin-sidebar">
          <nav>
            <button
              className={activeSection === "users" ? "active" : ""}
              type="button"
              onClick={() => setActiveSection("users")}
            >
              Người dùng
            </button>
            <button
              className={activeSection === "products" ? "active" : ""}
              type="button"
              onClick={() => setActiveSection("products")}
            >
              Sản phẩm
            </button>
            <button
              className={activeSection === "catalog" ? "active" : ""}
              type="button"
              onClick={() => setActiveSection("catalog")}
            >
              Danh mục
            </button>
          </nav>
        </aside>

        <section className="admin-workspace">
          {/* Chỉ render tab đang dùng để mỗi module tự tải và quản lý dữ liệu riêng. */}
          {activeSection === "users" ? (
            <AdminUsers canEditRoles={canEditRoles} />
          ) : activeSection === "products" ? (
            <AdminProducts canDeleteProducts={canDeleteProducts} />
          ) : (
            <AdminCatalog canDeleteCatalog={canDeleteCatalog} />
          )}
        </section>
      </div>
    </div>
  );
}
