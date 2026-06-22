import { useEffect, useMemo, useState } from "react";
import {
  createAdminManufacturer,
  deleteAdminManufacturer,
  fetchAdminManufacturers,
  updateAdminManufacturer,
} from "../../services/api";
import { formatDate } from "./adminUtils";

const emptyManufacturerForm = {
  tenHang: "",
};

export default function AdminManufacturers({ canDeleteCatalog }) {
  const [manufacturers, setManufacturers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [searchText, setSearchText] = useState("");
  const [modal, setModal] = useState(null);
  const [manufacturerForm, setManufacturerForm] = useState(emptyManufacturerForm);
  const [saving, setSaving] = useState(false);

  const loadManufacturers = async () => {
    setLoading(true);
    setError("");

    try {
      const data = await fetchAdminManufacturers();
      setManufacturers(data.manufacturers || []);
    } catch (err) {
      setError(err.message || "Không thể tải hãng sản xuất");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadManufacturers();
  }, []);

  const filteredManufacturers = useMemo(() => {
    const keyword = searchText.trim().toLowerCase();

    if (!keyword) return manufacturers;

    return manufacturers.filter((manufacturer) =>
      [manufacturer.maHangSanXuat, manufacturer.tenHang]
        .filter(Boolean)
        .join(" ")
        .toLowerCase()
        .includes(keyword)
    );
  }, [manufacturers, searchText]);

  const stats = [
    { label: "Hãng sản xuất", value: manufacturers.length },
    {
      label: "Sản phẩm",
      value: manufacturers.reduce((total, item) => total + (item.productCount || 0), 0),
    },
  ];

  const openCreate = () => {
    setError("");
    setManufacturerForm(emptyManufacturerForm);
    setModal({ mode: "create", item: null });
  };

  const openEdit = (manufacturer) => {
    setError("");
    setManufacturerForm({
      tenHang: manufacturer.tenHang || "",
    });
    setModal({ mode: "edit", item: manufacturer });
  };

  const closeModal = () => {
    setModal(null);
    setManufacturerForm(emptyManufacturerForm);
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError("");

    try {
      if (modal?.mode === "edit") {
        await updateAdminManufacturer(modal.item.maHangSanXuat, manufacturerForm);
      } else {
        await createAdminManufacturer(manufacturerForm);
      }

      await loadManufacturers();
      closeModal();
    } catch (err) {
      setError(err.message || "Không thể lưu hãng sản xuất");
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (manufacturer) => {
    if (!window.confirm(`Xóa hãng sản xuất "${manufacturer.tenHang}"?`)) {
      return;
    }

    setError("");

    try {
      await deleteAdminManufacturer(manufacturer.maHangSanXuat);
      await loadManufacturers();
    } catch (err) {
      setError(err.message || "Không thể xóa hãng sản xuất");
    }
  };

  return (
    <>
      <div className="admin-section-header catalog-subheader">
        <div>
          <span className="admin-kicker">Hãng</span>
          <h3>Nhà sản xuất</h3>
        </div>
        <div className="admin-actions">
          <button className="admin-refresh" type="button" onClick={loadManufacturers}>
            Tải lại
          </button>
          <button className="admin-primary-action" type="button" onClick={openCreate}>
            Thêm hãng
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
          <label htmlFor="manufacturer-search">Tìm kiếm</label>
          <input
            id="manufacturer-search"
            type="search"
            value={searchText}
            onChange={(event) => setSearchText(event.target.value)}
            placeholder="Tên hãng hoặc mã hãng"
          />
        </div>
      </div>

      {error && <div className="admin-alert">{error}</div>}

      <div className="catalog-panel">
        <div className="admin-table-wrap compact-table">
          {loading ? (
            <div className="admin-empty">Đang tải hãng sản xuất...</div>
          ) : filteredManufacturers.length === 0 ? (
            <div className="admin-empty">Không có hãng sản xuất phù hợp</div>
          ) : (
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Hãng sản xuất</th>
                  <th>Sản phẩm</th>
                  <th>Cập nhật</th>
                  <th>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                {filteredManufacturers.map((manufacturer) => (
                  <tr key={manufacturer.maHangSanXuat}>
                    <td>
                      <div className="admin-user-cell product-cell">
                        <span>
                          {String(manufacturer.tenHang || "H")
                            .charAt(0)
                            .toUpperCase()}
                        </span>
                        <div>
                          <strong>{manufacturer.tenHang}</strong>
                          <small>{manufacturer.maHangSanXuat}</small>
                        </div>
                      </div>
                    </td>
                    <td>{manufacturer.productCount || 0}</td>
                    <td>{formatDate(manufacturer.updatedAt)}</td>
                    <td>
                      <div className="row-actions">
                        <button type="button" onClick={() => openEdit(manufacturer)}>
                          Sửa
                        </button>
                        {canDeleteCatalog && (
                          <button
                            className="danger"
                            type="button"
                            onClick={() => handleDelete(manufacturer)}
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
                <span className="admin-kicker">Hãng sản xuất</span>
                <h3>
                  {modal.mode === "edit"
                    ? "Cập nhật hãng sản xuất"
                    : "Thêm hãng sản xuất"}
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
                <div className="form-field span-2">
                  <label htmlFor="tenHang">Tên hãng sản xuất</label>
                  <input
                    id="tenHang"
                    value={manufacturerForm.tenHang}
                    onChange={(event) =>
                      setManufacturerForm({
                        tenHang: event.target.value,
                      })
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
                  {saving ? "Đang lưu..." : "Lưu hãng sản xuất"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </>
  );
}
