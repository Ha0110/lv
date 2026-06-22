import { useState } from "react";
import AdminAttributes from "./AdminAttributes";
import AdminCategories from "./AdminCategories";
import AdminManufacturers from "./AdminManufacturers";

const catalogTabs = [
  { id: "categories", label: "Danh mục" },
  { id: "manufacturers", label: "Hãng sản xuất" },
  { id: "attributes", label: "Thuộc tính" },
];

export default function AdminCatalog({ canDeleteCatalog }) {
  const [activeCatalogTab, setActiveCatalogTab] = useState("categories");

  return (
    <>
      <div className="admin-section-header">
        <div>
          <span className="admin-kicker">Phân loại</span>
          <h2>Danh mục, hãng sản xuất và thuộc tính</h2>
        </div>
      </div>

      <div className="catalog-tabs">
        {catalogTabs.map((tab) => (
          <button
            key={tab.id}
            className={activeCatalogTab === tab.id ? "active" : ""}
            type="button"
            onClick={() => setActiveCatalogTab(tab.id)}
          >
            {tab.label}
          </button>
        ))}
      </div>

      {activeCatalogTab === "categories" ? (
        <AdminCategories canDeleteCatalog={canDeleteCatalog} />
      ) : activeCatalogTab === "manufacturers" ? (
        <AdminManufacturers canDeleteCatalog={canDeleteCatalog} />
      ) : (
        <AdminAttributes canDeleteCatalog={canDeleteCatalog} />
      )}
    </>
  );
}
