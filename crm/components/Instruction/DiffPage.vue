<script setup lang="ts">
const route = useRoute()
const item = ref<InstructionDiffResource>()

async function loadData() {
  const { data } = await useHttp<InstructionDiffResource>(
      `instructions/diff/${route.params.id}`,
  )
  if (data.value) {
    item.value = data.value
  }
}

nextTick(loadData)
</script>

<template>
  <div v-if="item">
    <div class="diff-header">
      <div>
        <b class="text-black">
          {{ item.prev.title }}
        </b>
        <br>
        версия {{ item.prev.index }}
      </div>
      <div>
        <b class="text-black">
          {{ item.current.title }}
        </b>
        <br>
        версия {{ item.current.index }}
      </div>
    </div>
    <div v-if="item.diff" v-html="item.diff" />
    <div v-else class="no-diff">
      <div>без изменений</div>
      <div>без изменений</div>
    </div>
  </div>
</template>

<style lang="scss">
.doc-diff {
  padding-top: 20px;

  &__header {
    margin-bottom: 12px;
    display: flex;
    width: 100%;
    font-weight: normal;

    & > div {
      flex: 1;
      // text-align: center;
      text-align: left;
    }
  }
}

$diff-bg-color: #f2f4f8;
$diff-text-color: invert($diff-bg-color) !default;

$diff-bg-color-ins-base: #8e8 !default;
$diff-bg-color-del-base: #e88 !default;
$diff-bg-color-rep-base: #fbdb65 !default;

$diff-op-highlight-ratio: 90% !default;
$diff-op-normal-ratio: 25% !default;

// emphasized colors for detailed inline difference
$diff-bg-color-ins-highlight: mix(
  $diff-bg-color-ins-base,
  $diff-bg-color,
  $diff-op-highlight-ratio
) !default;
$diff-bg-color-del-highlight: mix(
  $diff-bg-color-del-base,
  $diff-bg-color,
  $diff-op-highlight-ratio
) !default;

// colors for operation rows
$diff-bg-color-ins: mix(
  $diff-bg-color-ins-base,
  $diff-bg-color,
  $diff-op-normal-ratio
) !default;
$diff-bg-color-del: mix(
  $diff-bg-color-del-base,
  $diff-bg-color,
  $diff-op-normal-ratio
) !default;
$diff-bg-color-rep: mix(
  $diff-bg-color-rep-base,
  $diff-bg-color,
  $diff-op-normal-ratio
) !default;

$diff-table-head-color: mix($diff-bg-color, $diff-text-color, 65%) !default;
$diff-table-sidebar-color: #fafafa;
$diff-border-color: #e0e0e0;

// color for the nonexistent block
// for example, there are a deleted line that has no corresponding one
$diff-bg-color-none-block: mix(
  $diff-bg-color,
  $diff-table-sidebar-color,
  80%
) !default;
$diff-bg-color-none-block-alternative: mix(
  $diff-bg-color,
  $diff-table-sidebar-color,
  55%
) !default;

.diff-wrapper.diff {
  background: repeating-linear-gradient(
    -45deg,
    $diff-bg-color-none-block,
    $diff-bg-color-none-block 0.5em,
    $diff-bg-color-none-block-alternative 0.5em,
    $diff-bg-color-none-block-alternative 1em
  );
  border-collapse: collapse;
  border-spacing: 0;
  // border: 1px solid $diff-border-color;
  border: none;
  color: $diff-text-color;
  empty-cells: show;
  font-family: 'ibm-plex';
  font-weight: normal;
  font-size: 13px;
  width: 100%;
  word-break: break-all;

  th {
    font-weight: 700;
  }

  td {
    vertical-align: baseline;
  }

  td,
  th {
    border-collapse: separate;
    border: none;
    // padding: 1px 2px;
    background: $diff-bg-color;

    // make empty cell has height
    &:empty:after {
      content: ' ';
      visibility: hidden;
    }

    a {
      color: #000;
      cursor: inherit;
      pointer-events: none;
    }
  }

  th {
    display: none;
  }

  thead th {
    background: $diff-table-head-color;
    border-bottom: 1px solid $diff-border-color;
    padding: 4px;
    text-align: left;
  }

  tbody {
    &.skipped {
      border-top: 1px solid $diff-border-color;

      td,
      th {
        display: none;
      }
    }

    th {
      background: $diff-table-sidebar-color;
      // border-right: 1px solid $diff-border-color;
      text-align: right;
      vertical-align: top;
      width: 4em;

      &.sign {
        background: $diff-bg-color;
        border-right: none;
        padding: 1px 0;
        text-align: center;
        width: 1em;

        &.del {
          background: $diff-bg-color-del;
        }

        &.ins {
          background: $diff-bg-color-ins;
        }
      }
    }
  }

  &.diff-html {
    white-space: pre-wrap;

    &.diff-combined {
      .change.change-rep {
        .rep {
          white-space: normal;
        }
      }
    }

    .change {
      &.change-eq {
        .old,
        .new {
          background: white;
        }
      }

      .old {
        background: $diff-bg-color-del;
        width: 45vw;
        // border-right: 1px solid #e0e0e0;
      }

      .n-old,
      .n-new {
        color: gray;
        font-weight: 500;
      }

      .new {
        background: $diff-bg-color-ins;
        width: 45vw;
      }

      .old,
      .new {
        padding: 0 20px;
      }

      .rep {
        background: $diff-bg-color-rep;
      }

      .old,
      .new,
      .rep {
        &.none {
          background: white;
          cursor: default;
        }
      }

      ins,
      del {
        font-weight: bold;
        text-decoration: none;
      }

      ins {
        background: $diff-bg-color-ins-highlight;
      }

      del {
        background: $diff-bg-color-del-highlight;
      }
    }
  }
}

.diff-header {
  display: flex;
  position: sticky;
  top: 0;
  box-shadow: 0 0 10px 10px white;
  z-index: 1;
  background: white;
  padding-top: 20px;
  padding-bottom: 10px;
  margin-bottom: 10px;

  & > div {
    flex: 1;
    text-align: left;
    color: gray;
    padding-left: 20px;
  }
}

.no-diff {
  display: flex;

  & > div {
    flex: 1;
    text-align: center;
    padding: 30px 0;
    color: gray;
  }
}
</style>
