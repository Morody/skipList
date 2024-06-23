# SkipList

Список пропусков - вероятностная структура данных, позволяющая за $O(log(n))$ времени выполнять операции добавления, удаления, и поиска элементов.

## Node

```php
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
```

Данная структура является многоуровневой. Каждый элемент должен ссылаться не только на предыдущий и следующий элементы, но а также на вышестоящий и нижестоящий элементы. Поле `$key` - значение данного узла.

## Структура SkipList

```php
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
```

В классе `SkipList` объявляем поля `$head` и `$tail`. По мере добавления уровней, всегда самый верхний уровень будет оставаться без узлов. Следующий элемент *головы* это *хвост* списка, а предыдущий элемент *хвоста* - это *голова* списка. У *головы* списка на всех уровнях будет наибольшое целое число, поддерживаемое в этой сборке PHP - `int(9223372036854775807) in 64 bit systems`. У *хвоста* списка наименьшое целое число - `int(-9223372036854775808) in 64 bit systems`.

### SkipSearch

```php
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
```

![img]()

В качестве начального элемента поиска берется самый левый верхний элемент. Происходит спуск на самый нижний уровень, попутно проверяя элементы на каждом уровне, до тех пор пока текущее значение не станет меньше или равно искомому. 


### Добавление уровня в список

```php
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
```

![img]()

Создаем новый `$head` и `$tail` для добавленного уровня. Расставляем ссылки для *головы* и *хвоста*. Новый добавленный уровень находиться выше уже существующих. 


### SkipInsert

```php
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
    } while((bool)rand(0,1) == true);

}
```

В функции вставки определяем, есть ли уже в списке вставляемый элемент. Если есть - возвращаем. Если нет, то `$q` - элемент, который является предыдущим для вставляемого. После этого мы производим поиск позиции элемента, который будет предыдущим для элемента, который является вышестоящим для вставляемого элемента.

![img]()


Так как *список с пропусками* - вероятностная структура, появление элементов с самого нижнего на уровнях выше определяется вероятностью. В данном случае  `(bool)rand(0,1) == true` вероятность =  $\dfrac{1}{2}$

### insertAfterAbove

```php
public function insertAfterAbove(Node $q, Node $position, int $key){

    $newNode = new Node($key);
    $nodeBeforeNewNode = $position->below->below;

    $this->setBeforeAfterRef($q, $newNode);
    $this->setBelowAboveRef($position, $newNode, $nodeBeforeNewNode, $key);
}
```

![img]()

В данной функции мы создаем новый вставляемый элемент. Определяем элемент, позиция которого - предыдущий элементу, который находиться ниже вставляемого элемента. Также вызываем функции для определения ссылок для вставляемого элемента - предыдущий, последующий, нижестоящий и вышестоящий.

### Установление ссылок

```php
 public function setBeforeAfterRef(Node $q, Node $newNode){

    $newNode->next = $q->next;
    $newNode->prev = $q;
    $q->next->prev = $newNode;
    $q->next = $newNode;
}
```
`$q` - это предыдущий элемент для нового узла. С помощью него устанавливаем ссылки для нового узла на следующий элемент и на предыдущий. Для элемента, который идет после нового, задаем ссылку на новый элемент в качестве предыдущего. Для элемента, который идет до нового элемента, задаем ссылку на новый элемент в качестве следующего.

```php
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
```

![img]()

Проверяем на существование элемент, который находиться ниже нового элемента. Если его существование определено, то двигаем по уровню ниже к элементу, который находиться ниже нового элемента. После того как позиция была определена, мы ставим ссылку для нового элемента на элемент, который находиться ниже. Ставим ссылку для элемента, который находиться ниже, на вышестоящий новый элемент.

### Удаление узлов

Ищем удаляемый элемент. Если его нет - возвращаем null. Если он есть, приступаем к удалению ссылок на предыдущий, последующий, нижестоящий, вышестоящий элементов.

```php
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
```

Screens

