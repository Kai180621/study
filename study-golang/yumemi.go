package main

import (
	"encoding/csv"
	"flag"
	"fmt"
	"io"
	"log"
	"math"
	"os"
	"sort"
	"strconv"
)

const (
	MaxRanking = 10
)

// プレイヤーごとのスコア情報
type ScoreData struct {
	sum   int
	count int
}

type ScoreMap map[string]ScoreData

type MeanScoreMap map[int][]string

func main() {
	// CLI引数からCSVのファイル名を取得
	flag.Parse()

	// fileを開く
	file, err := os.Open(flag.Arg(0))
	if err != nil {
		log.Fatal(err)
	}
	defer file.Close()

	r := csv.NewReader(file)
	// ヘッダー行の取得と検証
	header, _ := r.Read()
	validateHeader(header)

	// スコアを記録する連想配列を作成
	scoreMap := createScoreMap(r) // { playerID: ScoreData }

	// 平均値をキーとした連想配列の作成
	meanScoreMap := createMeanScoreMap(scoreMap) // { meanScore: playerIds[] }

	// 平均値の配列を作成してソートする
	meanScoreArr := make([]int, 0, len(meanScoreMap))
	for meanScore := range meanScoreMap {
		meanScoreArr = append(meanScoreArr, meanScore)
	}
	sort.Sort(sort.Reverse(sort.IntSlice(meanScoreArr)))

	// 出力
	output(meanScoreMap, meanScoreArr)
}

// ヘッダーの検証
func validateHeader(header []string) {
	if header[0] != "create_timestamp" || header[1] != "player_id" || header[2] != "score" {
		log.Fatal("header is invalid")
	}
}

func createScoreMap(r *csv.Reader) ScoreMap {
	scoreMap := make(map[string]ScoreData, 5000)
	for {
		// 1行ずつ読み込む
		record, err := r.Read()
		if err == io.EOF {
			break
		} else if err != nil {
			log.Fatal(err)
		}
		score, _ := strconv.Atoi(record[2])
		// scores[]が初期化されていない場合は初期化する
		scoreData, ok := scoreMap[record[1]]
		if !ok {
			scoreMap[record[1]] = ScoreData{sum: 0, count: 0}
		}
		scoreData.sum += score
		scoreData.count += 1
		scoreMap[record[1]] = scoreData
	}
	return scoreMap
}

func createMeanScoreMap(scoreMap ScoreMap) MeanScoreMap {
	meanScoreMap := make(map[int][]string, 500)
	for playerId, scoreData := range scoreMap {
		meanScore := int(math.Round(float64(scoreData.sum) / float64(scoreData.count)))
		// playerIds[]が初期化されていない場合は初期化する
		if _, ok := meanScoreMap[meanScore]; !ok {
			meanScoreMap[meanScore] = make([]string, 0, 5)
		}
		meanScoreMap[meanScore] = append(meanScoreMap[meanScore], playerId)
	}
	return meanScoreMap
}

func output(meanScoreMap MeanScoreMap, meanScoreArr []int) {
	fmt.Println("rank,player_id,mean_score")
	manCount := 0 // 出力済みの人数
	ranking := 1  // ランキング
	for _, meanScore := range meanScoreArr {
		for _, playerId := range meanScoreMap[meanScore] {
			str := fmt.Sprintf("%d,%s,%d", ranking, playerId, meanScore)
			fmt.Println(str)
			manCount += 1
		}
		if manCount >= MaxRanking {
			break
		}
		ranking += len(meanScoreMap[meanScore])
}
