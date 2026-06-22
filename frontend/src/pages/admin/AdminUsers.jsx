import { useEffect, useMemo, useState } from "react";
import { fetchAdminUsers, updateAdminUserRole } from "../../services/api";
import { formatDate, roleLabels, roles } from "./adminUtils";

export default function AdminUsers({ canEditRoles }) {
  const [users, setUsers] = useState([]);
  const [summary, setSummary] = useState({
    total: 0,
    customer: 0,
    admin: 0,
    owner: 0,
  });
  const [usersLoading, setUsersLoading] = useState(false);
  const [usersError, setUsersError] = useState("");
  const [searchText, setSearchText] = useState("");
  const [roleFilter, setRoleFilter] = useState("all");
  const [savingSdt, setSavingSdt] = useState("");

  // Tải danh sách người dùng và số lượng theo từng vai trò.
  const loadUsers = async () => {
    setUsersLoading(true);
    setUsersError("");

    try {
      const data = await fetchAdminUsers();
      setUsers(data.users || []);
      setSummary(data.summary || summary);
    } catch (err) {
      setUsersError(err.message || "Không thể tải danh sách người dùng");
    } finally {
      setUsersLoading(false);
    }
  };

  useEffect(() => {
    loadUsers();
  }, []);

  // Lọc trên dữ liệu đã tải để thao tác tìm kiếm không gọi API liên tục.
  const filteredUsers = useMemo(() => {
    const keyword = searchText.trim().toLowerCase();

    return users.filter((user) => {
      const matchesRole = roleFilter === "all" || user.role === roleFilter;
      const source = [user.hoTen, user.email, user.sdt, user.role]
        .filter(Boolean)
        .join(" ")
        .toLowerCase();
      const matchesSearch = !keyword || source.includes(keyword);

      return matchesRole && matchesSearch;
    });
  }, [roleFilter, searchText, users]);

  const verifiedCount = useMemo(
    () => users.filter((user) => user.emailVerified).length,
    [users]
  );

  const userStats = [
    { label: "Tất cả", value: summary.total || users.length },
    { label: "Khách hàng", value: summary.customer || 0 },
    { label: "Quản trị", value: summary.admin || 0 },
    { label: "Chủ cửa hàng", value: summary.owner || 0 },
    { label: "Đã xác thực", value: verifiedCount },
  ];

  const handleRoleChange = async (user, nextRole) => {
    if (user.role === nextRole) return;

    // Cập nhật giao diện trước, nếu API lỗi sẽ hoàn tác lại danh sách cũ.
    const previousUsers = users;
    const previousRole = user.role;

    setSavingSdt(user.sdt);
    setUsersError("");
    setUsers((items) =>
      items.map((item) =>
        item.sdt === user.sdt ? { ...item, role: nextRole } : item
      )
    );

    try {
      const data = await updateAdminUserRole(user.sdt, nextRole);

      setUsers((items) =>
        items.map((item) =>
          item.sdt === user.sdt ? { ...item, ...data.user } : item
        )
      );
      setSummary((current) => ({
        ...current,
        [previousRole]: Math.max((current[previousRole] || 1) - 1, 0),
        [nextRole]: (current[nextRole] || 0) + 1,
      }));
    } catch (err) {
      setUsers(previousUsers);
      setUsersError(err.message || "Không thể cập nhật vai trò");
    } finally {
      setSavingSdt("");
    }
  };

  return (
    <>
      <div className="admin-section-header">
        <div>
          <span className="admin-kicker">Tài khoản</span>
          <h2>Bảng người dùng</h2>
        </div>

        <button className="admin-refresh" type="button" onClick={loadUsers}>
          Tải lại
        </button>
      </div>

      <div className="admin-stats">
        {userStats.map((item) => (
          <div className="admin-stat" key={item.label}>
            <span>{item.label}</span>
            <strong>{item.value}</strong>
          </div>
        ))}
      </div>

      <div className="admin-toolbar">
        <div className="admin-search">
          <label htmlFor="admin-search">Tìm kiếm</label>
          <input
            id="admin-search"
            type="search"
            value={searchText}
            onChange={(event) => setSearchText(event.target.value)}
            placeholder="Tên, email hoặc số điện thoại"
          />
        </div>

        <div className="admin-filter">
          <label htmlFor="role-filter">Vai trò</label>
          <select
            id="role-filter"
            value={roleFilter}
            onChange={(event) => setRoleFilter(event.target.value)}
          >
            <option value="all">Tất cả</option>
            {roles.map((role) => (
              <option key={role.value} value={role.value}>
                {role.label}
              </option>
            ))}
          </select>
        </div>
      </div>

      {usersError && <div className="admin-alert">{usersError}</div>}

      <div className="admin-table-wrap">
        {usersLoading ? (
          <div className="admin-empty">Đang tải danh sách người dùng...</div>
        ) : filteredUsers.length === 0 ? (
          <div className="admin-empty">Không có người dùng phù hợp</div>
        ) : (
          <table className="admin-table">
            <thead>
              <tr>
                <th>Người dùng</th>
                <th>Số điện thoại</th>
                <th>Vai trò</th>
                <th>Xác thực</th>
                <th>Điểm</th>
                <th>Ngày tạo</th>
              </tr>
            </thead>
            <tbody>
              {filteredUsers.map((user) => (
                <tr key={user.sdt}>
                  <td>
                    <div className="admin-user-cell">
                      <span>
                        {String(user.hoTen || user.email || "U")
                          .charAt(0)
                          .toUpperCase()}
                      </span>
                      <div>
                        <strong>{user.hoTen || "Chưa có tên"}</strong>
                        <small>{user.email}</small>
                      </div>
                    </div>
                  </td>
                  <td>{user.sdt}</td>
                  <td>
                    {canEditRoles ? (
                      <select
                        className={`role-select role-${user.role}`}
                        value={user.role}
                        disabled={savingSdt === user.sdt}
                        onChange={(event) =>
                          handleRoleChange(user, event.target.value)
                        }
                      >
                        {roles.map((role) => (
                          <option key={role.value} value={role.value}>
                            {role.label}
                          </option>
                        ))}
                      </select>
                    ) : (
                      <span className={`role-badge role-${user.role}`}>
                        {roleLabels[user.role] || user.role}
                      </span>
                    )}
                  </td>
                  <td>
                    <span
                      className={`verify-badge ${
                        user.emailVerified ? "verified" : "pending"
                      }`}
                    >
                      {user.emailVerified ? "Đã xác thực" : "Chờ xác thực"}
                    </span>
                  </td>
                  <td>{user.diemTichLuy || 0}</td>
                  <td>{formatDate(user.createdAt)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </>
  );
}
