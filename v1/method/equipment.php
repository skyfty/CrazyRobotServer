<?php
//封装装备相关方法
/**
 * 获取已装备的装备列表
 * @param int $userId 用户ID
 * @return array 已装备的装备数据
 */
function getEquippedItems($userId) {
    // 示例：从数据库或缓存中查询已装备装备
    $equipped = [];
    // TODO: 根据实际业务查询已装备装备
    return $equipped;
}

/**
 * 获取未装备的装备数量
 * @param int $userId 用户ID
 * @return int 未装备的装备数量
 */
function getUnequippedCount($userId) {
    // 示例：计算未装备装备数量
    $count = 0;
    // TODO: 根据实际业务统计未装备装备数量
    return $count;
}
