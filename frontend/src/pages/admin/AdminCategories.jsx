import { useEffect, useMemo, useState } from "react";
import {
  createAdminCategory,
  deleteAdminCategory,
  fetchAdminCategories,
  updateAdminCategory,
} from "../../services/api";
import { formatDate } from "./adminUtils";

const emptyCategoryForm = {
  tenDanhMuc: "",
  moTa: "",
};

export default function AdminCategories({ canDeleteCatalog }) {
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [searchText, setSearchText] = useState("");
  const [modal, setModal] = useState(null);
  const [categoryForm, setCategoryForm] = useState(emptyCategoryForm);
  const [saving, setSaving] = useState(false);

  const loadCategories = async () => {
    setLoading(true);
    setError("");

    try {
      const data = await fetchAdminCategories();
      setCategories(data.categories || []);
    } catch (err) {
      setError(err.message || "Không thể tải danh mục");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadCategories();
  }, []);

  const filteredCategories = useMemo(() => {
    const keyword = searchText.trim().toLowerCase();

    if (!keyword) return categories;

    return categories.filter((category) =>
      [category.maDanhMuc, category.tenDanhMuc, category.moTa]
        .filter(Boolean)
        .join(" ")
        .toLowerCase()
        .includes(keyword)
    );
  }, [categories, searchText]);

  const stats = [
    { label: "Danh mục", value: categories.length },
    {
      label: "Sản phẩm",
      value: categories.reduce((total, item) => total + (item.productCount || 0), 0),
    },
    {
      label: "Thuộc tính",
      value: categories.reduce((total, item) => total + (item.attributeCount || 0), 0),
    },
  ];

  const openCreate = () => {
    setError("");
    setCategoryForm(emptyCategoryForm);
    setModal({ mode: "create", item: null });
  };

  const openEdit = (category) => {
    setError("");
    setCategoryForm({
      tenDanhMuc: category.tenDanhMuc || "",
      moTa: category.moTa || "",
    });
    setModal({ mode: "edit", item: category });
  };

  const closeModal = () => {
    setModal(null);
    setCategoryForm(emptyCategoryForm);
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError("");

    try {
      if (modal?.mode === "edit") {
        await updateAdminCategory(modal.item.maDanhMuc, categoryForm);
      } else {
        await createAdminCategory(categoryForm);
      }

      await loadCategories();
      closeModal();
    } catch (err) {
      setError(err.message || "Không thể lưu danh mục");
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (category) => {
    if (!window.confirm(`Xóa danh mục "${category.tenDanhMuc}"?`)) {
      return;
    }

    setError("");

    try {
      await deleteAdminCategory(category.maDanhMuc);
      await loadCategories();
    } catch (err) {
      setError(err.message || "Không thể xóa danh mục");
    }
  };

  return (
    <>
      <div className="admin-section-header catalog-subheader">
        <div>
          <span className="admin-kicker">Danh mục</span>
          <h3>Nhóm sản phẩm</h3>
        </div>
        <div className="admin-actions">
          <button className="admin-refresh" type="button" onClick={loadCategories}>
            Tải lại
          </button>
          <button className="admin-primary-action" type="button" onClick={openCreate}>
            Thêm danh mục
          </button>
        </div>
      </div>

      <div className="admin-stats product-stats">
        {stats.map((item) => (
          <div className="admin-stat" key={item.label}>
            <span>{item.label}</span>
            <strong>{item.value}</strong>
          </div>
        ))}
      </div>

      <div className="admin-toolbar">
        <div className="admin-search">
          <label htmlFor="category-search">Tìm kiếm</label>
          <input
            id="category-search"
            type="search"
            value={searchText}
            onChange={(event) => setSearchText(event.target.value)}
            placeholder="Tên danh mục, mã hoặc mô tả"
          />
        </div>
      </div>

      {error && <div className="admin-alert">{error}</div>}

      <div className="catalog-panel">
        <div className="admin-table-wrap compact-table">
          {loading ? (
            <div className="admin-empty">Đang tải danh mục...</div>
          ) : filteredCategories.length === 0 ? (
            <div className="admin-empty">Không có danh mục phù hợp</div>
          ) : (
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Danh mục</th>
                  <th>Sản phẩm</th>
                  <th>Thuộc tính</th>
                  <th>Cập nhật</th>
                  <th>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                {filteredCategories.map((category) => (
                  <tr key={category.maDanhMuc}>
                    <td>
                      <div className="admin-user-cell product-cell">
                        <span>
                          {String(category.tenDanhMuc || "D")
                            .charAt(0)
                            .toUpperCase()}
                        </span>
                        <div>
                          <strong>{category.tenDanhMuc}</strong>
                          <small>{category.maDanhMuc}</small>
                          {category.moTa && <small>{category.moTa}</small>}
                        </div>
                      </div>
                    </td>
                    <td>{category.productCount || 0}</td>
                    <td>{category.attributeCount || 0}</td>
                    <td>{formatDate(category.updatedAt)}</td>
                    <td>
                      <div className="row-actions">
                        <button type="button" onClick={() => openEdit(category)}>
                          Sửa
                        </button>
                        {canDeleteCatalog && (
                          <button
                            className="danger"
                            type="button"
                            onClick={() => handleDelete(category)}
                          >
                            Xóa
                          </button>
                        )}
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      </div>

      {modal && (
        <div className="admin-modal-backdrop">
          <div className="admin-modal compact-modal" role="dialog" aria-modal="true">
            <div className="admin-modal-header">
              <div>
                <span className="admin-kicker">Danh mục</span>
                <h3>{modal.mode === "edit" ? "Cập nhật danh mục" : "Thêm danh mục"}</h3>
              </div>
              <button
                className="admin-icon-button"
                type="button"
                onClick={closeModal}
                aria-label="Đóng"
              >
                ×
              </button>
            </div>

            <form className="product-form" onSubmit={handleSubmit}>
              <div className="form-grid">
                <div className="form-field span-2">
                  <label htmlFor="tenDanhMuc">Tên danh mục</label>
                  <input
                    id="tenDanhMuc"
                    value={categoryForm.tenDanhMuc}
                    onChange={(event) =>
                      setCategoryForm((current) => ({
                        ...current,
                        tenDanhMuc: event.target.value,
                      }))
                    }
                    required
                  />
                </div>
                <div className="form-field span-2">
                  <label htmlFor="moTaDanhMuc">Mô tả</label>
                  <textarea
                    id="moTaDanhMuc"
                    rows="4"
                    value={categoryForm.moTa}
                    onChange={(event) =>
                      setCategoryForm((current) => ({
                        ...current,
                        moTa: event.target.value,
                      }))
                    }
                  />
                </div>
              </div>

              <div className="admin-modal-actions">
                <button className="admin-refresh" type="button" onClick={closeModal}>
                  Hủy
                </button>
                <button className="admin-primary-action" type="submit" disabled={saving}>
                  {saving ? "Đang lưu..." : "Lưu danh mục"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </>
  );
}
