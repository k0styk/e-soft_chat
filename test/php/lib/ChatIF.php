<?php
/**
 * Chat interface
 */
interface ChatIF {
    /**
     * Put message to the room
     * @param string $uid user uuid
     * @param string $roomId room uuid
     * @param string $msg
     * @return boolean
     */
    public function putMessage($uid, $roomId, $msg);
    /**
     * Get messages by room id 
     * @param string $roomId room uuid 
     * @return array
     */
    public function getMessages($roomId);
    /**
     * Create new room 
     * @return string room uuid
     */
    public function createRoom();
    /**
     * Delete room
     * @param string $roomId room uuid 
     * @return bool
     */
    public function deleteRoom($roomId);
    /**
     * Put room member
     * @param string $roomId room uuid
     * @param string $uid user uuid 
     * @return bool
     */
    public function putRoomMember($roomId, $uid);
    /**
     * Delete room member 
     * @param string $roomId room uuid
     * @param string $uid user uuid
     */
    public function deleteRoomMember($roomId, $uid); 
    /**
     * Blocking member on the room
     * @param string $roomId room uuid
     * @param string $uid user uuid
     * @param string $expiration time stamp
     */
    public function blockRoomMember($roomId, $uid, $expiration);
}