安裝方式：
1. 請將pick_students資料夾放置在moodle中的blocks資料夾之內
2. 點選 網站管理 > 通知  => 確認安裝成功
3. 安裝完成後請將『挑選學生』區塊新增至合適的位置(建議可放置在「系統管理」或「課程」頁面之下)

使用方式：
1. 點選「挑選學生」區塊中的「已選課的用戶」。
2. 依序選擇「學校」與「班級」，會列表出在系統中符合的學生名單。
3. 接著選擇「類別」、「課程」與「角色」，按下「核準」。(若是在課程頁面使用，只需要選擇角色)
4. 會再進行一次確認，若確認無誤後，按下「是」，將會執行選課與角色指派。

(註：由於需要有學校、班級、老師與學生的對照數據，所以請在lib/eip.php中的SCHOOL_LIST_URL(學校列表)、BAN_LIST_URL(學校中所有班級列表)、BAN_STUDENT_URL(班級中所有學生列表)、TEACHER_LIST_URL(學校中所有老師列表)、STUDENT_LIST_URL(學校老師所有班級列表)取代成符合環境的值)

此專案由 宜蘭縣政府教育處(http://www.ilc.edu.tw/) 提供
委託 智新資通服份有限公司(http://www.steps.com.tw/) 開發
