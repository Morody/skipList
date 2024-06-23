<?php

class Node{

    public ?Node $prev;
    public ?Node $next;
    public ?Node $below;
    public ?Node $above;

    public int $key;

    public function __construct(int $key){
        $this->key = $key;
        $this->prev = null;
        $this->next = null;
        $this->above = null;
        $this->below = null;
    }

}

class SkipList{

    public Node $head;
    public Node $tail;

    public int $NEG_INF = PHP_INT_MIN;
    public int $POS_INF = PHP_INT_MAX;

    public int $heightOfList = 0;

    public function __construct() {
        $this->head = new Node($this->NEG_INF);
        $this->tail = new Node($this->POS_INF);
        $this->head->next = $this->tail;
        $this->tail->prev = $this->head;
    }

    public function skipSearch(int $key){
        $node = $this->head;


        while ($node->below != null){

            $node = $node->below;

            while($node->next->key <= $key){
                $node = $node->next;
            }

        }

        return $node;
    }


    public function addEmptyLevel(){
        $newNodeHead = new Node($this->NEG_INF);
        $newNodeTail = new Node($this->POS_INF);

        $newNodeHead->next = $newNodeTail;
        $newNodeTail->prev = $newNodeHead;

        $newNodeHead->below = $this->head;
        $newNodeTail->below = $this->tail;

        $this->head->above = $newNodeHead;
        $this->tail->above = $newNodeTail;

        $this->head = $newNodeHead;
        $this->tail = $newNodeTail;

    }

    public function canIncreaseLevel(int $level){

        if($level >= $this->heightOfList){
            $this->heightOfList++;

            $this->addEmptyLevel();
        }
    }


    public function setBeforeAfterRef(Node $q, Node $newNode){

        $newNode->next = $q->next;
        $newNode->prev = $q;
        $q->next->prev = $newNode;
        $q->next = $newNode;
    }

    public function setBelowAboveRef(Node $position, Node $newNode, ?Node $nodeBeforeNewNode, int $key){

        if($nodeBeforeNewNode != null){
            while(true){
                if ($nodeBeforeNewNode->next->key != $key){
                    $nodeBeforeNewNode = $nodeBeforeNewNode->next;
                } else {
                    break;
                }
            }

            $newNode->below = $nodeBeforeNewNode->next;
            $nodeBeforeNewNode->next->above = $newNode;
        }

        if($position != null){
            if($position->next->key == $key){
                $newNode->above = $position->next;
            }
        }
    }

    public function insertAfterAbove(Node $q, Node $position, int $key){
        $newNode = new Node($key);
        $nodeBeforeNewNode = $position->below->below;

        $this->setBeforeAfterRef($q, $newNode);
        $this->setBelowAboveRef($position, $newNode, $nodeBeforeNewNode, $key);
    }

    public function skipInsert(int $key){

        $position = $this->skipSearch($key);
        
        $level = -1;
        $numberOfHead = -1;

        if($position->key == $key){
            return $position;
        }

        do{
            $level++;
            $numberOfHead++;

            $this->canIncreaseLevel($level);

            $q = $position;

            while($position->above == null){
                $position = $position->prev;
            }

            $position = $position->above;

            $this->insertAfterAbove($q, $position, $key);

        }while((bool)rand(0,1) == true);

    }

    public function removeRefToNode(Node $nodeToBeRemoved){

        $nodeAfterRemove = $nodeToBeRemoved -> next;
        $nodeBeforeRemove = $nodeToBeRemoved -> prev;

        $nodeAfterRemove->prev = $nodeBeforeRemove;
        $nodeBeforeRemove->next = $nodeAfterRemove;

    }

    public function remove(int $key){
        $nodeToBeRemoved = $this->skipSearch($key);

        if ($nodeToBeRemoved->key != $key){
            return null;
        }

        $this->removeRefToNode($nodeToBeRemoved);

        while($nodeToBeRemoved != null){
            $this->removeRefToNode($nodeToBeRemoved);

            if ($nodeToBeRemoved->above != null){
                $nodeToBeRemoved = $nodeToBeRemoved->above;
            } else break;
        }
    }

    public function printSkipList(){
        $skipList = '';
        $skipList .= "\n\n SkipList starting with top-left most node.\n";
        $starting = $this->head;

        $highestLevel = $starting;
        $level = $this->heightOfList;

        while($highestLevel != null){
            $skipList .= "\nLevel: " . $level . "\n";

            while($starting != null){
                $skipList .= $starting->key;

                if ($starting->next != null){
                    $skipList .= " : ";
                }

                $starting = $starting->next;
            }

            $highestLevel = $highestLevel->below;
            $starting = $highestLevel;
            $level--;
        }

        print($skipList);
    }

}

$skipList = new SkipList();
$skipList->skipInsert(5);
$skipList->skipInsert(15);
$skipList->skipInsert(25);

$skipList->printSkipList();

$skipList->remove(5);

$skipList->printSkipList();


?>