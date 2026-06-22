import { useEffect, useMemo, useState } from "react";
import {
  createAdminAttribute,
  deleteAdminAttribute,
  fetchAdminAttributes,
  fetchAdminCategories,
  updateAdminAttribute,
} from "../../services/api";

const emptyAttributeForm = {
  maDanhMuc: "",
  tenThuocTinh: "",
};

export default function AdminAttributes({ canDeleteCatalog }) {
  const [attributes, setAttributes] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [searchText, setSearchText] = useState("");
  const [categoryFilter, setCategoryFilter] = useState("all");
  const [modal, setModal] = useState(null);
  const [attributeForm, setAttributeForm] = useState(emptyAttributeForm);
  const [saving, setSaving] = useState(false);

  const loadAttributes = async () => {
    setLoading(true);
    setError("");

    try {
      const [attributeData, categoryData] = await Promise.all([
        fetchAdminAttributes(),
        fetchAdminCategories(),
      ]);
      setAttributes(attributeData.attributes || []);
      setCategories(categoryData.categories || []);
    } catch (err) {
      setError(err.message || "Không thể tải thuộc tính");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadAttributes();
  }, []);

  const filteredAttributes = useMemo(() => {
    const keyword = searchText.trim().toLowerCase();

    return attributes.filter((attribute) => {
      const matchesCategory =
        categoryFilter === "all" || attribute.maDanhMuc === categoryFilter;
      const source = [
        attribute.maTT,
        attribute.tenThuocTinh,
        attribute.tenDanhMuc,
        attribute.maDanhMuc,
      ]
        .filter(Boolean)
        .join(" ")
        .toLowerCase();
      const matchesSearch = !keyword || source.includes(keyword);

      return matchesCategory && matchesSearch;
    });
  }, [attributes, categoryFilter, searchText]);

  const stats = [
    { label: "Thuộc tính", value: attributes.length },
    { label: "Danh mục", value: categories.length },
    {
      label: "Đang được dùng",
      value: attributes.filter((item) => (item.usageCount || 0) > 0).length,
    },
  ];

  const openCreate = () => {
    setError("");
    setAttributeForm({
      ...emptyAttributeForm,
      maDanhMuc: categories[0]?.maDanhMuc || "",
    });
    setModal({ mode: "create", item: null });
  };

  const openEdit = (attribute) => {
    setError("");
    setAttributeForm({
      maDanhMuc: attribute.maDanhMuc || "",
      tenThuocTinh: attribute.tenThuocTinh || "",
    });
    setModal({ mode: "edit", item: attribute });
  };

  const closeModal = () => {
    setModal(null);
    setAttributeForm(emptyAttributeForm);
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError("");

    try {
      if (modal?.mode === "edit") {
        await updateAdminAttribute(modal.item.maTT, attributeForm);
      } else {
        await createAdminAttribute(attributeForm);
      }

      await loadAttributes();
      closeModal();
    } catch (err) {
      setError(err.message || "Không thể lưu thuộc tính");
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (attribute) => {
    if (!window.confirm(`Xóa thuộc tính "${attribute.tenThuocTinh}"?`)) {
      return;
    }

    setError("");

    try {
      await deleteAdminAttribute(attribute.maTT);
      await loadAttributes();
    } catch (err) {
      setError(err.message || "Không thể xóa thuộc tính");
    }
  };

  return (
    <>
      <div className="admin-section-header catalog-subheader">
        <div>
          <span className="admin-kicker">Thuộc tính</span>
          <h3>Thông số theo danh mục</h3>
        </div>
        <div className="admin-actions">
          <button className="admin-refresh" type="button" onClick={loadAttributes}>
            Tải lại
          </button>
          <button className="admin-primary-action" type="button" onClick={openCreate}>
            Thêm thuộc tính
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
          <label htmlFor="attribute-search">Tìm kiếm</label>
          <input
            id="attribute-search"
            type="search"
            value={searchText}
            onChange={(event) => setSearchText(event.target.value)}
            placeholder="Tên thuộc tính, mã hoặc danh mục"
          />
        </div>

        <div className="admin-filter">
          <label htmlFor="attribute-category-filter">Danh mục</label>
          <select
            id="attribute-category-filter"
            value={categoryFilter}
            onChange={(event) => setCategoryFilter(event.target.value)}
          >
            <option value="all">Tất cả</option>
            {categories.map((category) => (
              <option key={category.maDanhMuc} value={category.maDanhMuc}>
                {category.tenDanhMuc}
              </option>
            ))}
          </select>
        </div>
      </div>

      {error && <div className="admin-alert">{error}</div>}

      <div className="catalog-panel">
        <div className="admin-table-wrap compact-table">
          {loading ? (
            <div className="admin-empty">Đang tải thuộc tính...</div>
          ) : filteredAttributes.length === 0 ? (
            <div className="admin-empty">Không có thuộc tính phù hợp</div>
          ) : (
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Thuộc tính</th>
                  <th>Danh mục</th>
                  <th>Đang dùng</th>
                  <th>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                {filteredAttributes.map((attribute) => (
                  <tr key={attribute.maTT}>
                    <td>
                      <div className="admin-user-cell product-cell">
                        <span>
                          {String(attribute.tenThuocTinh || "T")
                            .charAt(0)
                            .toUpperCase()}
                        </span>
                        <div>
                          <strong>{attribute.tenThuocTinh}</strong>
                          <small>{attribute.maTT}</small>
                        </div>
                      </div>
                    </td>
                    <td>{attribute.tenDanhMuc || "Chưa có"}</td>
                    <td>{attribute.usageCount || 0}</td>
                    <td>
                      <div className="row-actions">
                        <button type="button" onClick={() => openEdit(attribute)}>
                          Sửa
                        </button>
                        {canDeleteCatalog && (
                          <button
                            className="danger"
                            type="button"
                            onClick={() => handleDelete(attribute)}
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
                <span className="admin-kicker">Thuộc tính</span>
                <h3>
                  {modal.mode === "edit" ? "Cập nhật thuộc tính" : "Thêm thuộc tính"}
                </h3>
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
                <div className="form-field">
                  <label htmlFor="maDanhMucThuocTinh">Danh mục</label>
                  <select
                    id="maDanhMucThuocTinh"
                    value={attributeForm.maDanhMuc}
                    onChange={(event) =>
                      setAttributeForm((current) => ({
                        ...current,
                        maDanhMuc: event.target.value,
                      }))
                    }
                    required
                  >
                    <option value="">Chọn danh mục</option>
                    {categories.map((category) => (
                      <option key={category.maDanhMuc} value={category.maDanhMuc}>
                        {category.tenDanhMuc}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="form-field">
                  <label htmlFor="tenThuocTinh">Tên thuộc tính</label>
                  <input
                    id="tenThuocTinh"
                    value={attributeForm.tenThuocTinh}
                    onChange={(event) =>
                      setAttributeForm((current) => ({
                        ...current,
                        tenThuocTinh: event.target.value,
                      }))
                    }
                    required
                  />
                </div>
              </div>

              <div className="admin-modal-actions">
                <button className="admin-refresh" type="button" onClick={closeModal}>
                  Hủy
                </button>
                <button className="admin-primary-action" type="submit" disabled={saving}>
                  {saving ? "Đang lưu..." : "Lưu thuộc tính"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </>
  );
}
