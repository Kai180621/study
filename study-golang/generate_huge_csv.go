package main

import (
	"encoding/csv"
	"fmt"
	"log"
	"math/rand"
	"os"
	"strconv"
)

const (
	MaxPlayers  = 10000
	Lines       = 30000000
	WriteBuffer = 10000
	Timestamp   = "1998/01/01 11:59"
)

func main() {
	// O_WRONLY:書き込みモード開く, O_CREATE:無かったらファイルを作成
	file, err := os.OpenFile("./game_score_log.csv", os.O_WRONLY|os.O_CREATE, 0600)
	if err != nil {
		log.Fatal(err)
	}
	defer file.Close()

	err = file.Truncate(0) // ファイルを空っぽにする(実行2回目以降用)
	if err != nil {
		log.Fatal(err)
	}

	w := csv.NewWriter(file)
	w.Write([]string{"create_timestamp", "player_id", "score"}) // ヘッダーの書き込み

	lineCount := 0
	for lineCount < Lines {
		for i := 0; i < WriteBuffer; i++ {
			playerId := fmt.Sprintf("player%d", rand.Intn(MaxPlayers))
			score := strconv.Itoa(rand.Intn(10000))
			w.Write([]string{Timestamp, playerId, score})
		}
		w.Flush()
		lineCount += WriteBuffer
	}
}
